<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Utilities;

use Chefkoch\Morphoji\Converter;

class EmojiUtility
{
    public static function encode($message): string
    {
        $message = (new Converter()) -> fromEmojis($message);

        // converting to old version - buh!
        $matches = [];
        $re = '/:emoji-(.[^:]*):/i';
        preg_match_all($re, $message, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $dec = hexdec($match[1]);
            $message = str_replace($match[0], '{emoji:' . $dec . '}', $message);
        }

        return $message;
    }
    public static function decode($message): string
    {
        // Emojis finden
        $matches = [];
        $re = '/{emoji:([\d]*)}/mi';
        preg_match_all($re, $message, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $hex = dechex((int)$match[1]);
            $message = str_replace($match[0], ':emoji-' . $hex . ':', $message);
        }

        return (new Converter()) -> toEmojis($message);
    }
}
