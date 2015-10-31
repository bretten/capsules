<?php
namespace Capsules\Authentication;

/**
 * Represents an authentication token
 *
 * @package Capsules\Authentication
 * @author Brett Namba
 */
class Token {

    /**
     * The byte count used to generate the underlying authentication token
     *
     * @var int
     */
    private $byteCount;

    /**
     * The string representation of the authentication token
     *
     * @var string
     */
    private $tokenString;

    /**
     * The default byte count
     *
     * @var int
     */
    private static $DEFAULT_BYTE_COUNT = 16;

    /**
     * Constructor
     *
     * @param int $byteCount The byte count of the authentication token
     */
    public function __construct($byteCount = 0) {
        if ($byteCount == 0) {
            $this->byteCount = Token::$DEFAULT_BYTE_COUNT;
        }
        $this->generateToken();
    }

    /**
     * Gets the string representation of the authentication token
     *
     * @return string The string representation of the token
     */
    public function getTokenString() {
        return $this->tokenString;
    }

    /**
     * Generates the authentication token
     */
    private function generateToken() {
        $this->tokenString = bin2hex(openssl_random_pseudo_bytes($this->byteCount));
    }

    /**
     * Factory method to get a new Token
     *
     * @param int $byteCount The number of bytes to use when generating the authentication token
     * @return Token The newly generated Token
     */
    public static function instance($byteCount = 0) {
        return new Token($byteCount);
    }

}
