<?php

namespace App\Contracts;

interface FileContract
{
    public function download(int $userId, string $uuid);
    public function move(array $data);
    public function delete(array $data);
    public function rename(array $data);
    public function copy(array $data);
    public function store(array $data);
    public function addToFavorites(array $data);
}
