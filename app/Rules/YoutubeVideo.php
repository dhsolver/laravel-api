<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YoutubeVideo implements Rule
{
    // examples:
    // https://youtu.be/Bey4XXJAqS8
    // https://www.youtube.com/watch?v=Bey4XXJAqS8
    public static $normalHosts = ['youtube.com', 'www.youtube.com'];
    public static $shortHosts = ['youtu.be', 'www.youtu.be'];
    public static $shortRegex = '/^\/([a-zA-Z0-9_-]+)$/i';
    public static $normalRegex = '/^v\=([a-zA-Z0-9_-]+)$/i';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $url = parse_url($value);

        if (empty($url) || ! isset($url['scheme'])) {
            return false;
        }

        if ($url['scheme'] != 'https' && $url['scheme'] != 'http') {
            return false;
        }

        if (in_array($url['host'], self::$shortHosts)) {
            // short url
            if (preg_match(self::$shortRegex, $url['path'])) {
                return true;
            }
        } elseif (in_array($url['host'], self::$normalHosts)) {
            // normal url
            if ($url['path'] != '/watch') {
                return false;
            }

            if (preg_match(self::$normalRegex, $url['query'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid YouTube video.';
    }

    /**
     * Helper function format and unify Youtube URLs.
     *
     * @param [String] $url
     * @return String
     */
    public static function formatUrl($url)
    {
        $url = parse_url($url);

        if (empty($url) || ! isset($url['scheme'])) {
            return '';
        }

        if ($url['scheme'] != 'https' && $url['scheme'] != 'http') {
            return '';
        }

        if (in_array($url['host'], self::$shortHosts)) {
            // short url
            if (preg_match(self::$shortRegex, $url['path'], $matches)) {
                return 'https://www.youtube.com/watch?v=' . $matches[1];
            }
        } elseif (in_array($url['host'], self::$normalHosts)) {
            if (preg_match(self::$normalRegex, $url['query'], $matches)) {
                return 'https://www.youtube.com/watch?v=' . $matches[1];
            }
        }

        return '';
    }
}
