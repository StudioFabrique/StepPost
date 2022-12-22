<?php

namespace App\Services;

class MessageService
{
    public function GetSuccessMessage(string $type, int $action, string $replace = ""): array
    {
        $messages = array();
        $index = 1;
        foreach ($this->DecodeMessageArray('error')[$type] as $message) {
            $messages[$index] = $message;
        }
        return ['errorMessage' => str_replace('[nom]', $replace, $messages[$action])];
    }

    public function GetErrorMessage(string $type, int $action, string $replace = ""): array
    {
        $messages = array();
        $index = 1;
        foreach ($this->DecodeMessageArray('error')[$type] as $message) {
            $messages[$index] = $message;
        }
        return ['errorMessage' => str_replace('[nom]', $replace, $messages[$action]), 'isError' => true];
    }

    // Récupère et convertie le fichier json contenant les messages et le converti en tableau
    private function DecodeMessageArray(string $type): array
    {
        $messages = json_decode(file_get_contents(__DIR__ . "/messages.json"), true);
        return $type == "success" ? $messages['Messages Informations'] : $messages['Messages Erreurs'];
    }
}
