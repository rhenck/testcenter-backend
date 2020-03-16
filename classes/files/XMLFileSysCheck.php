<?php /** @noinspection PhpUnhandledExceptionInspection */


class XMLFileSysCheck extends XMLFile {

    public function getUnitId() {
        
        $myreturn = '';
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                $unitAttr = $configNode['unit'];
                if (isset($unitAttr)) {
                    $myreturn = strtoupper((string) $unitAttr);
                }
            }
        }
        return $myreturn;
    }


    public function getSaveKey() {
        
        $myreturn = '';
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                $savekeyAttr = $configNode['savekey'];
                if (isset($savekeyAttr)) {
                    $myreturn = (string) $savekeyAttr;
                }
            }
        }
        return $myreturn;
    }


    public function hasSaveKey() {
        $myKey = $this->getSaveKey();
        return strlen($myKey) > 0;
    }


    public function hasUnit() {
        $myUnitId = $this->getUnitId();
        return strlen($myUnitId) > 0;
    }

    // ####################################################
    public function getCustomTexts()
    {
        $myreturn = [];
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                foreach($configNode->children() as $ct) {
                    if ($ct->getName() === 'CustomText') {
                        array_push($myreturn, [
                            'key' => (string) $ct['key'],
                            'value' => (string) $ct
                        ]);
                    }
                }
            }
        }
        return $myreturn;
    }


    public function getSkipNetwork() {

        $myreturn = false;
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                $qomAttr = $configNode['skipnetwork'];
                if (isset($qomAttr)) {
                    $qom = (string) $qomAttr;
                    $myreturn = ($qom == 'true');
                }
            }
        }
        return $myreturn;
    }


    public function getQuestions() {

        $myreturn = [];
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                foreach($configNode->children() as $q) { 
                    if ($q->getName() === 'Q') {
                        array_push($myreturn, [
                            'id' => (string) $q['id'],
                            'type' => (string) $q['type'],
                            'prompt' => (string) $q['prompt'],
                            'required' => (boolean) $q['required'],
                            'options' => explode('#', (string) $q)
                        ]);
                    }
                }
            }
        }
        return $myreturn;
    }

    // ####################################################
    public function getSpeedtestUploadParams()
    {
        $myreturn = [
            'min' => 0,
            'good' => 0,
            'maxDevianceBytesPerSecond' => 0,
            'maxErrorsPerSequence' => 0,
            'maxSequenceRepetitions' => 0,
            'sequenceSizes' >= []
        ];
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                $speedDefNode = $configNode->UploadSpeed[0];
                if (isset($speedDefNode)) {
                    if (isset($speedDefNode['min'])) {
                        $myreturn['min'] = (integer) $speedDefNode['min'];
                    }
                    if (isset($speedDefNode['good'])) {
                        $myreturn['good'] = (integer) $speedDefNode['good'];
                    }
                    if (isset($speedDefNode['maxDevianceBytesPerSecond'])) {
                        $myreturn['maxDevianceBytesPerSecond'] = (integer) $speedDefNode['maxDevianceBytesPerSecond'];
                    }
                    if (isset($speedDefNode['maxErrorsPerSequence'])) {
                        $myreturn['maxErrorsPerSequence'] = (integer) $speedDefNode['maxErrorsPerSequence'];
                    }
                    if (isset($speedDefNode['maxSequenceRepetitions'])) {
                        $myreturn['maxSequenceRepetitions'] = (integer) $speedDefNode['maxSequenceRepetitions'];
                    }
                    $sequenceSizesString = (string) $speedDefNode;
                    $matches = [];
                    if ((strlen($sequenceSizesString) > 0) && (preg_match_all("/[0-9]+/",
                                $sequenceSizesString,$matches) > 0)) {
                        $myreturn['sequenceSizes'] = $matches[0];
                    }
                }
            }
        }
        return $myreturn;
    }

    public function getSpeedtestDownloadParams()
    {
        $myreturn = [
            'min' => 0,
            'good' => 0,
            'maxDevianceBytesPerSecond' => 0,
            'maxErrorsPerSequence' => 0,
            'maxSequenceRepetitions' => 0,
            'sequenceSizes' >= []
        ];
        if ($this->_isValid and ($this->xmlfile != false) and ($this->_rootTagName == 'SysCheck')) {
            $configNode = $this->xmlfile->Config[0];
            if (isset($configNode)) {
                $speedDefNode = $configNode->DownloadSpeed[0];
                if (isset($speedDefNode)) {
                    if (isset($speedDefNode['min'])) {
                        $myreturn['min'] = (integer) $speedDefNode['min'];
                    }
                    if (isset($speedDefNode['good'])) {
                        $myreturn['good'] = (integer) $speedDefNode['good'];
                    }
                    if (isset($speedDefNode['maxDevianceBytesPerSecond'])) {
                        $myreturn['maxDevianceBytesPerSecond'] = (integer) $speedDefNode['maxDevianceBytesPerSecond'];
                    }
                    if (isset($speedDefNode['maxErrorsPerSequence'])) {
                        $myreturn['maxErrorsPerSequence'] = (integer) $speedDefNode['maxErrorsPerSequence'];
                    }
                    if (isset($speedDefNode['maxSequenceRepetitions'])) {
                        $myreturn['maxSequenceRepetitions'] = (integer) $speedDefNode['maxSequenceRepetitions'];
                    }
                    $sequenceSizesString = (string) $speedDefNode;
                    $matches = [];
                    if ((strlen($sequenceSizesString) > 0) && (preg_match_all("/[0-9]+/",
                                $sequenceSizesString,$matches) > 0)) {
                        $myreturn['sequenceSizes'] = $matches[0];
                    }
                }
            }
        }
        return $myreturn;
    }


    public function getUnitData() {

        $myreturn = [
            'player_id' => '',
            'def' => '',
            'player' => ''
        ];
        $myUnitId = $this->getUnitId();
        if (strlen($myUnitId) > 0) {
            $workspaceDirName = dirname(dirname($this->_filename));
            if (isset($workspaceDirName) && is_dir($workspaceDirName)) {
                $unitFolder = $workspaceDirName . '/Unit';
                $resourcesFolder = $workspaceDirName . '/Resource';
                $mydir = opendir($unitFolder);
                if ($mydir !== false) {
                    $unitNameUpper = strtoupper($myUnitId);

                    while (($entry = readdir($mydir)) !== false) {
                        $fullfilename = $unitFolder . '/' . $entry;
                        if (is_file($fullfilename) && (strtoupper(substr($entry, -4)) == '.XML')) {
                            $xFile = new XMLFile($fullfilename);
                            if ($xFile->isValid()) {
                                $uKey = $xFile->getId();
                                if ($uKey == $unitNameUpper) {
                                    $definitionNode = $xFile->xmlfile->Definition[0];
                                    if (isset($definitionNode)) {
                                        $typeAttr = $definitionNode['player'];
                                        if (isset($typeAttr)) {
                                            $myreturn['player_id'] = (string) $typeAttr;
                                            $myreturn['def'] = (string) $definitionNode;
                                        }
                                    } else {
                                        $definitionNode = $xFile->xmlfile->DefinitionRef[0];
                                        if (isset($definitionNode)) {
                                            $typeAttr = $definitionNode['player'];
                                            if (isset($typeAttr)) {
                                                $myreturn['player_id'] = (string) $typeAttr;
                                                $unitfilename = strtoupper((string) $definitionNode);
                                                $myRdir = opendir($resourcesFolder);
                                                if ($myRdir !== false) {
                                                    while (($anyfile = readdir($myRdir)) !== false) {
                                                        if (strtoupper($anyfile) == $unitfilename) {
                                                            $fullanyfilename = $resourcesFolder . '/' . $anyfile;
                                                            $myreturn['def'] = file_get_contents($fullanyfilename);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }            
                                    break;
                                }
                            }
                        }
                    }
                    if (isset($myreturn['player_id'])) {
                        $playerCode = $this->getResource($myreturn['player_id']  . '.HTML', $resourcesFolder);
                        if ($playerCode) {
                            $myreturn['player'] = $playerCode;
                        }
                    }
                }
            }
        }
        
        return $myreturn;
    }

    // TODO copy from tc_get.php -> share in separate file
    private function normaliseFileName($fn, $v) {

        $myreturn = strtoupper($fn);
        if ($v) {
            $firstDotPos = strpos($myreturn, '.');
            if ($firstDotPos) {
                $lastDotPos = strrpos($myreturn, '.');
                if ($lastDotPos > $firstDotPos) {
                    $myreturn = substr($myreturn, 0, $firstDotPos) . substr($myreturn, $lastDotPos);
                }
            }
        }
        return $myreturn;
    }


    // TODO copy from tc_get.php -> share in separate file
    private function getResource($resourceid, $resourceFolder, $versionning = 'v') {

        $path_parts = pathinfo($resourceid); // extract filename if path is given
        $resourceFileName = $this->normaliseFileName($path_parts['basename'], $versionning != 'f');

        if (file_exists($resourceFolder) and (strlen($resourceFileName) > 0)) {
            $mydir = opendir($resourceFolder);
            if ($mydir !== false) {

                while (($entry = readdir($mydir)) !== false) {
                    $normfilename = $this->normaliseFileName($entry, $versionning != 'f');
                    if ($normfilename == $resourceFileName) {
                        $fullfilename = $resourceFolder . '/' . $entry;
                        return file_get_contents($fullfilename);
                    }
                }
            }
        }
        return "";
    }


    public function saveReport($key, $title, $envData, $netData, $questData, $unitData): void {

        if (strlen($key) <= 0) {

            throw new HttpError("No key `$key`", 400);
        }

        if (strtoupper($key) == strtoupper($this->getSaveKey())) {

            throw new HttpError("Wrong key `$key`", 400);
        }

        $workspaceDirName = dirname(dirname($this->_filename));

        if (!isset($workspaceDirName) or !is_dir($workspaceDirName)) {

            throw new Exception("Directory not found `$workspaceDirName`");
        }

        $sysCheckFolder = $workspaceDirName . '/' . $this->getRoottagName();

        if (file_exists($sysCheckFolder)) {
            $reportFolder = $sysCheckFolder . '/reports';
            if (!file_exists($reportFolder)) {
                if (!mkdir($reportFolder)) {
                    $reportFolder = '';
                }
            }
            if (strlen($reportFolder) > 0) {
                $reportFilename = $reportFolder . '/' . uniqid('report_', true) . '.json';
                $reportData = [
                    'date' => date('Y-m-d H:i:s', time()),
                    'checkId' => $this->getId(),
                    'checkLabel' => $this->getLabel(),
                    'title' => $title,
                    'envData' => $envData,
                    'netData' => $netData,
                    'questData' => $questData,
                    'unitData' => $unitData
                ];
                if (file_put_contents($reportFilename, json_encode($reportData)) !== false) {
                    throw new Exception("Could not write to file `$reportData`");
                }
            }
        }
    }
}
