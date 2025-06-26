<?php

namespace Eclipse\Core\Foundation\Settings;

use Eclipse\Core\Settings\Repositories\UserSiteSettingsRepository;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

trait IsUserSiteScoped
{
    public static function repository(): ?string
    {
        return 'user_tenant';
    }

    /**
     * Get settings for a specific user
     *
     * @param  int  $userId  The ID of the user to get settings for
     */
    public static function forUser(int $userId): static
    {
        // Create a new instance of UserSettings
        $settings = new static;

        // Get the repository from the settings instance
        $repository = $settings->getRepository();

        // Make sure it's a UserSettingsRepository
        if (! $repository instanceof UserSiteSettingsRepository) {
            throw new RuntimeException('Repository must be an instance of UserSiteSettingsRepository');
        }

        // Configure the repository to use the specified user
        $userRepository = $repository->forUser($userId);

        // Get the properties directly from the repository
        $properties = collect($userRepository->getPropertiesInGroup(static::group()));

        // Process the properties (decrypt, cast, etc.)
        $reflectionProperties = collect((new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(fn (ReflectionProperty $property) => [$property->getName() => $property]);

        // Set the properties on the settings instance
        foreach ($reflectionProperties as $name => $property) {
            if (isset($properties[$name])) {
                $settings->$name = $properties[$name];
            } elseif ($property->hasDefaultValue()) {
                $settings->$name = $property->getDefaultValue();
            }
        }

        return $settings;
    }
}
