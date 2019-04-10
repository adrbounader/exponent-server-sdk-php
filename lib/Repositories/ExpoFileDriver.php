<?php

namespace ExponentPhpSDK\Repositories;

use ExponentPhpSDK\ExpoRepository;

class ExpoFileDriver implements ExpoRepository
{
    /**
     * Default file path where tokens will be stored
     */
    const DEFAULT_STORAGE = __DIR__ . '/../../storage/tokens.json';

    /** @var string */
    private $storage;

    /**
     * ExpoFileDriver constructor.
     * @param string|null $customStoragePath Custom file path where tokens will be stored
     */
    public function __construct(string $customStoragePath = null) {
        $this->storage = isset($customStoragePath) ? self::DEFAULT_STORAGE : $customStoragePath;
    }

    /**
     * Stores an Expo token with a given identifier
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store($key, $value): bool
    {
        $storageInstance = $this->getRepository();

        // Check for existing tokens
        if (isset($storageInstance->{$key})) {
            // If there is a single token, make it an array so we can push the additional tokens in it
            if (!is_array($storageInstance->{$key})) {
                $storageInstance->{$key} = [$storageInstance->{$key}];
            }

            // Prevent duplicates
            if (!in_array($value, $storageInstance->{$key})) {
                // Add new token to existing key
                array_push($storageInstance->{$key}, $value);
            }
        } else {
            // First token for this key
            $storageInstance->{$key} = [$value];
        }

        $file = $this->updateRepository($storageInstance);

        return (bool) $file;
    }

    /**
     * Retrieves an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return array|string|null
     */
    public function retrieve(string $key)
    {
        $token = null;

        $storageInstance = $this->getRepository();

        $token = $storageInstance->{$key}?? null;

        return $token;
    }

    /**
     * Removes an Expo token with a given identifier
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget(string $key, string $value = null): bool
    {
        $storageInstance = $storageInstance = $this->getRepository();

        // Delete a single token with this key and check if there are multiple tokens associated with this key
        if($value && isset($storageInstance->{$key}) && is_array($storageInstance->{$key}) && count($storageInstance->{$key}) > 0)
        {
            // Find our token in list of tokens
            $index = array_search($value, $storageInstance->{$key});

            if ($index !== false) {
                // Remove single token from list
                unset($storageInstance->{$key}[$index]);

                if (count($storageInstance->{$key}) === 0) {
                    // No more tokens left, remove key
                    unset($storageInstance->{$key});
                } else {
                    // Reset array key after removing an key
                    $storageInstance->{$key} = array_values($storageInstance->{$key});
                }

                $this->updateRepository($storageInstance);

                return !isset($storageInstance->{$key}) || !in_array($value, $storageInstance->{$key});
            }
        } else {
            // Delete all tokens with this key
            unset($storageInstance->{$key});

            $this->updateRepository($storageInstance);

            return !isset($storageInstance->{$key});
        }

        return false;
    }

    /**
     * Gets the storage file contents and converts it into an object
     *
     * @return object
     */
    private function getRepository()
    {
        if (!file_exists($this->storage)) {
            $content = $this->createFile();
        }
        else {
            $file = file_get_contents($this->storage);
            $content = json_decode($file);
        }
        return $content;
    }

    /**
     * Updates the storage file with the new contents
     *
     * @param $contents
     *
     * @return bool|int
     */
    private function updateRepository($contents)
    {
        $record = json_encode($contents);
        return file_put_contents($this->storage, $record);
    }

    /**
     * Creates the storage file
     *
     * @return object
     */
    private function createFile()
    {
        $file = fopen($this->storage, "w");
        fputs($file, '{}');
        fclose($file);
        return json_decode('{}');
    }
}
