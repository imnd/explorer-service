<?php

namespace App\Services;

use App\Models\File;
use Carbon\Carbon;

class FileHelper
{
    public function isFolder(File $file)
    {
        return $file->type == File::TYPE_FOLDER;
    }

    public function isEmpty(File $file)
    {
        if (!$this->isFolder($file)) {
            return true;
        }
        return $this->getChildren($file)->count() == 0;
    }

    public function hasDir($file, $name)
    {
        return !empty(
            File::where('parent_id', $file->id)
                ->where('type', File::TYPE_FOLDER)
                ->where('name', $name)
                ->first()
        );
    }

    public function hasFile($folder, $name)
    {
        return !empty(
            File::where('parent_id', $folder->id)
                ->where('type', '!=', File::TYPE_FOLDER)
                ->where('name', $name)
                ->first()
        );
    }

    public function getFullPath(File $file)
    {
        $parents = collect([$file]);

        while (!is_null($file->parent)) {
            $parents->push($file->parent);
            $file = $file->parent;
        }

        return $parents->reverse();
    }

    public function getFullPathSymbolic(File $file)
    {
        $path = [];
        $collection = $this->getFullPath($file);
        foreach ($collection as $item) {
            $path[] = $item->name;
        }
        return implode('/', $path);
    }

    public function getChildren(File $file)
    {
        return File::whereParentId($file->id)->whereUserId($file->user_id)->get();
    }

    public function getAllChildren(File $file)
    {
        $children = $this->getChildren($file);
        foreach ($children as $child) {
            if ($this->isFolder($child)) {
                foreach ($this->getAllChildren($child) as $item) {
                    $children = $children->push($item);
                }
            }
        }
        return $children;
    }

    public function has(File $dir, File $file)
    {
        return !empty(
            File::where('parent_id', $dir->id)
                ->where('type', '=', $file->type)
                ->where('name', $file->name)
                ->when(($file->type != File::TYPE_FOLDER), function ($query) use ($file) {
                    return $query->where('extension', $file->extension);
                })
                ->first()
        );
    }

    public function generateCopyName(File $destination, File $file)
    {
        if (!$this->has($destination, $file)) {
            return $file->name;
        } else {
            $file->name .= ' (copy)';
            return $this->generateCopyName($destination, $file);
        }
    }

    public function getSize(File $file)
    {
        if (isset($file->payload['size'])) {
            return $file->payload['size'];
        }
        $children = $this->getAllChildren($file);
        $size = 0;
        foreach ($children as $child) {
            if (isset($child->payload['size'])) {
                $size += $child->payload['size'];
            }
        }
        return $size;
    }

    public function getLastModified(File $file)
    {
        if (isset($file->payload['last_modified'])) {
            return (new Carbon($file->payload['last_modified']))->toDateTimeString();
        }
        $children = $this->getAllChildren($file);
        if ($children->isEmpty()) {
            return $file->updated_at->toDateTimeString();
        }
        return (new Carbon(max($children->pluck('payload.last_modified')->toArray())))->toDateTimeString();
    }

    public function getFullName(File $file)
    {
        return $file->extension ? "{$file->name}.{$file->extension}" : $file->name;
    }
}
