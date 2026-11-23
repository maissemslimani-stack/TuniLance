<?php

if (! function_exists('highlight_mentions')) {
    /**
     * Wraps @proposal / @accept / @reject / @moderator in a green span so they
     * stand out in the chat thread. Case-insensitive, matches the word wherever
     * it appears in the message (start, middle, or end).
     */
    function highlight_mentions(string $text): string
    {
        $patterns = ['@proposal', '@accept', '@reject', '@moderator'];

        foreach ($patterns as $tag) {
            $text = preg_replace(
                '/'.preg_quote($tag, '/').'/i',
                '<span class="text-emerald-600 font-semibold">'.$tag.'</span>',
                $text
            );
        }

        return $text;
    }
}