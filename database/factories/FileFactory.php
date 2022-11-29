<?php

use Faker\Generator as Faker,
    App\Models\File,
    App\User,
    Illuminate\Support\Str;

$factory->define(File::class, function (Faker $faker, $attributes) {
    if (isset($attributes['user_id'])) {
        $user = User::find($attributes['user_id']);
    } else {
        $user = User::create([
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => bcrypt('secret'),
        ]);
    }
    $parentId = $attributes['parent_id'] ?? null;
    $name = isset($attributes['parent_id']) ? $faker->word : hash('md5', $user->id);
    $type = $attributes['type'] ?? File::TYPE_FOLDER;
    $externalId = ($type == File::TYPE_FOLDER) ? null : hash('md5', time());
    return [
        'name' => $name,
        'parent_id' => $parentId,
        'type' => $type,
        'uuid' => Str::orderedUuid()->toString(),
        'user_id' => $user->id,
        'external_id' => $externalId,
    ];
});
