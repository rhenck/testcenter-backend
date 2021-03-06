<?php

/** @noinspection PhpUnhandledExceptionInspection */


class Person extends DataCollectionTypeSafe {

    protected $id = 0;
    protected $token = "";
    protected $code = "";
    protected $group = "";

    protected $validTo = 0;

    function __construct(
        int $id,
        string $token,
        string $code,
        int $validTo,
        string $group
    ) {

        $this->id = $id;
        $this->token  = $token;
        $this->code = $code;
        $this->validTo = $validTo;
        $this->group = $group;
    }


    public function getId(): int {

        return $this->id;
    }


    public function getToken(): string {

        return $this->token;
    }


    public function getCode(): string {

        return $this->code;
    }


    public function getValidTo(): int {

        return $this->validTo;
    }

    public function getGroup(): string {

        return $this->group;
    }
}
