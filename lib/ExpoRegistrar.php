<?php
namespace ExponentPhpSDK;

use ExponentPhpSDK\Exceptions\ExpoRegistrarException;

class ExpoRegistrar
{
    /**
     * Repository that manages the storage and retrieval
     *
     * @var ExpoRepository
     */
    private $repository;

    /**
     * ExpoRegistrar constructor.
     *
     * @param ExpoRepository $repository
     */
    public function __construct(ExpoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Registers the given token for the given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return bool True if $token has been stored else false
     */
    public function registerInterest($interest, $token): bool
    {
        if (! $this->isValidExpoPushToken($token)) {
            throw ExpoRegistrarException::invalidToken();
        }

        return $this->repository->store($interest, $token);
    }

    /**
     * Removes token of a given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return bool
     */
    public function removeInterest($interest, $token = null)
    {
        return $this->repository->forget($interest, $token);
    }

    /**
     * Gets the tokens of the interests
     *
     * @param array $interests
     *
     * @throws ExpoRegistrarException
     *
     * @return array
     */
    public function getInterests(array $interests): array
    {
        $tokens = [];

        foreach ($interests as $interest) {
            $retrieved = $this->repository->retrieve($interest);

            if (!is_null($retrieved)) {
                if(is_string($retrieved)) {
                    $tokens[] = $retrieved;
                }

                if(is_array($retrieved)) {
                    foreach($retrieved as $token) {
                        if(is_string($token)) {
                            $tokens[] = $token;
                        }
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * Determines if a token is a valid Expo push token
     *
     * @param string $token
     *
     * @return bool
     */
    private function isValidExpoPushToken(string $token)
    {
        return  substr($token, 0, 18) ===  "ExponentPushToken[" && substr($token, -1) === ']';
    }
}
