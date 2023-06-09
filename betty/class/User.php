<?php

namespace Betty;

class User
{
    private \Betty\Database $database;
    private $data;
    private $followers;

    public function __construct(\Betty\Database $database, $id)
    {
        $this->database = $database;
        $this->data = $this->database->fetch("SELECT u.* FROM users u WHERE u.id = ?", [$id]);
        $this->followers = $this->database->fetch("SELECT COUNT(user) FROM subscriptions WHERE user = ?", [$id])['COUNT(user)'];
    }

    public function getUserArray(): array
    {
        return [
            "username" => $this->data["name"],
            "displayname" => $this->data["title"],
            "color" => $this->data["customcolor"],
            "followers" => $this->followers,
            "joined" => $this->data["joined"],
            "connected" => $this->data["lastview"],
        ];
    }
}