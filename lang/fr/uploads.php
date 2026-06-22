<?php

return [

    // Messages de rejet de fichiers téléversés (App\Services\UploadSecurityService).
    'invalid' => 'Le fichier téléversé est invalide ou corrompu.',
    'too_large' => 'Le fichier dépasse la taille maximale autorisée (:max).',
    'forbidden_type' => 'Ce type de fichier est interdit pour des raisons de sécurité.',
    'bad_extension' => 'Extension non autorisée. Formats acceptés : :list.',
    'dangerous_content' => 'Le contenu du fichier a été identifié comme potentiellement dangereux.',
    'mime_mismatch' => 'Le contenu du fichier (:actual) ne correspond pas à son extension (.:declared).',
    'malware_detected' => 'Un logiciel malveillant a été détecté dans le fichier (:threat).',
    'scan_unavailable' => "L'analyse antivirus est indisponible, le téléversement a été refusé.",

];
