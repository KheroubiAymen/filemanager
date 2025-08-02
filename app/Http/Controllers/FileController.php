<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    /**
     * Types de fichiers acceptés
     */
    protected $acceptedTypes = ['pdf', 'docx', 'png', 'jpg', 'jpeg', 'odt'];

    /**
     * Affiche la liste des fichiers
     */
    public function index(Request $request)
    {
        $query = File::where('user_id', Auth::id());
        
        // Filtrage par recherche
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtrage par type de fichier
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }
        
        // Filtrage par date
        if ($request->has('date') && !empty($request->date)) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'year':
                    $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
                    break;
            }
        }
        
        // Filtrage par taille
        if ($request->has('size') && !empty($request->size)) {
            switch ($request->size) {
                case 'small':
                    $query->where('size', '<', 1024 * 1024); // Moins de 1MB
                    break;
                case 'medium':
                    $query->whereBetween('size', [1024 * 1024, 10 * 1024 * 1024]); // Entre 1MB et 10MB
                    break;
                case 'large':
                    $query->where('size', '>', 10 * 1024 * 1024); // Plus de 10MB
                    break;
            }
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        // Vérifier que le champ de tri est valide pour éviter les injections SQL
        $allowedSortFields = ['name', 'type', 'size', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        $files = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Files/FilesList', [
            'files' => $files,
            'filters' => $request->only(['search', 'type', 'date', 'size', 'sort_field', 'sort_direction'])
        ]);
    }

    /**
     * Affiche le formulaire d'upload
     */
    public function create()
    {
        return Inertia::render('Files/Upload', [
            'acceptedTypes' => $this->acceptedTypes
        ]);
    }

    /**
     * Télécharge un ou plusieurs fichiers
     */
    public function store(Request $request)
    {
        $validationRules = [
            'files' => 'required|array|max:5',
            'files.*' => 'required|file|mimes:' . implode(',', $this->acceptedTypes) . '|max:10240',
        ];
        
        $validationMessages = [
            'files.required' => 'Vous devez sélectionner au moins un fichier.',
            'files.array' => 'Le format de la sélection de fichiers est invalide.',
            'files.max' => 'Vous ne pouvez pas uploader plus de 5 fichiers à la fois.',
            'files.*.required' => 'Chaque fichier doit être valide.',
            'files.*.file' => 'L\'élément uploadé doit être un fichier valide.',
            'files.*.mimes' => 'Le type du fichier n\'est pas accepté. Seuls les formats PDF, DOCX, PNG, JPG, JPEG et ODT sont autorisés.',
            'files.*.max' => 'La taille du fichier ne doit pas dépasser 10 Mo.',
        ];
        
        $request->validate($validationRules, $validationMessages);
        
        $uploadedFiles = [];
        
        foreach ($request->file('files') as $uploadedFile) {
            $path = $uploadedFile->store('files/' . Auth::id(), 'public');
            
            $file = new File();
            $file->name = $uploadedFile->getClientOriginalName();
            $file->path = $path;
            $file->type = $uploadedFile->getClientOriginalExtension();
            $file->size = $uploadedFile->getSize();
            $file->user_id = Auth::id();
            $file->save();
            
            $uploadedFiles[] = $file;
        }
        
        return redirect()->route('files.index')->with('success', 'Fichier(s) uploadé(s) avec succès.');
    }

    /**
     * Prévisualise un fichier
     */
    public function preview(File $file)
    {
        // Vérifier si l'utilisateur est autorisé à prévisualiser ce fichier
        if ($file->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier si le type de fichier est prévisualisable
        $previewableTypes = ['pdf', 'png', 'jpg', 'jpeg', 'gif'];
        if (!in_array(strtolower($file->type), $previewableTypes)) {
            abort(415, 'Ce type de fichier ne peut pas être prévisualisé.');
        }
        
        // Obtenir le chemin complet du fichier
        $filePath = Storage::disk('public')->path($file->path);
        
        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé.');
        }
        
        // Déterminer le type MIME
        $contentType = $this->getContentType($file->type);
        
        // Préparer la réponse avec le contenu du fichier
        $response = new Response(file_get_contents($filePath), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $file->name . '"',
        ]);
        
        return $response;
    }

    /**
     * Télécharge un fichier
     */
    public function download(File $file)
    {
        // Vérifier si l'utilisateur est autorisé à télécharger ce fichier
        if ($file->user_id !== Auth::id()) {
            abort(403);
        }
        
        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Supprime un fichier
     */
    public function destroy(File $file)
    {
        // Vérifier si l'utilisateur est autorisé à supprimer ce fichier
        if ($file->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Supprimer le fichier physique
        Storage::disk('public')->delete($file->path);
        
        // Supprimer l'enregistrement de la base de données
        $file->delete();
        
        return redirect()->route('files.index')->with('success', 'Fichier supprimé avec succès.');
    }
    
    /**
     * Détermine le type de contenu MIME en fonction de l'extension
     */
    private function getContentType($extension)
    {
        $extension = strtolower($extension);
        
        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
            case 'png':
                return 'image/png';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            default:
                return 'application/octet-stream';
        }
    }
}