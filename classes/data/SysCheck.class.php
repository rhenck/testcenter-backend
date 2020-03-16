<?php

class SysCheck extends AbstractDataCollection {

    public $name = '';
    public $label = '';
    public $canSave = false;
    public $hasUnit = false;
    public $skipNetwork = false;
    public $questions = [];
    public $downloadSpeed = [];
    public $uploadSpeed = [];
    public $customTexts;
    public $workspaceId = null;
}
