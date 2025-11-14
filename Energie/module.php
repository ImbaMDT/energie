<?php

declare(strict_types=1);

class Energie extends IPSModule
{
    public function Create()
    {
        parent::Create();
        // Timer ist vorbereitet, aber aktuell nicht aktiv (0ms = deaktiviert)
        $this->RegisterTimer('DeployScriptsTimer', 0, 'SDP_DeployScripts($_IPS["TARGET"]);');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->SendDebug('Energie', 'Starte automatisches Skript-Deployment...', 0);

        $moduleDir = __DIR__ . DIRECTORY_SEPARATOR . 'scripts';

        if (!is_dir($moduleDir)) {
            $this->SendDebug('Energie', 'Scripts-Verzeichnis nicht gefunden: ' . $moduleDir, 0);
            return;
        }

        $files = scandir($moduleDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $ident = pathinfo($file, PATHINFO_FILENAME);
                $existingID = @IPS_GetObjectIDByIdent($ident, $this->InstanceID);

                if ($existingID === false) {
                    $scriptID = IPS_CreateScript(0);
                    IPS_SetName($scriptID, $ident);
                    IPS_SetIdent($scriptID, $ident);
                    IPS_SetParent($scriptID, $this->InstanceID);

                    $code = file_get_contents($moduleDir . DIRECTORY_SEPARATOR . $file);
                    IPS_SetScriptContent($scriptID, $code);

                    $this->SendDebug('Energie', "Skript '$ident' neu erstellt (ID: $scriptID)", 0);
                } else {
                    $this->SendDebug('Energie', "Skript '$ident' existiert bereits (ID: $existingID)", 0);
                }
            }
        }
    }
}
