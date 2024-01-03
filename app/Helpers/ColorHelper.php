<?php

namespace App\Helpers;

use Exception;
use Filament\Support\Colors\Color;
use Filament\Support\Colors\ColorManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Color
 *
 * This class is a support class offering color validation and retrieval for Filament Panel
 * configuration. It is used by the Brand model to validate color configurations.
 */
class ColorHelper
{
    public static function validColorConfiguration(string $panel, string $condition, string $color_name): bool
    {
        return self::validColor($color_name)
            && self::validCondition($condition)
            && self::validPanelName($panel);
    }

    public static function getShades(string $name): ?array
    {
        if ($name === '') {
            return null;
        }

        if (defined(Color::class.'::'.ucfirst($name))) {
            return constant(Color::class.'::'.ucfirst($name));
        }

        if (static::hexColor($name)) {
            if (! Str::startsWith($name, '#')) {
                $name = '#'.$name;
            }

            return Color::hex($name);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public static function rgbToHex($r, $g = null, $b = null): string
    {
        if (is_string($r) && Str::contains($r, ',')) {
            $process = explode(',', $r);
            if (count($process) !== 3) {
                throw new Exception('Invalid RGB string format. Should be "R,G,B"');
            }
            $r = $process[0];
            $g = $process[1];
            $b = $process[2];
        }

        static::validateRgb($r, $g, $b);

        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);

        return '#'.str_pad($r, 2, '0', STR_PAD_LEFT).str_pad($g, 2, '0', STR_PAD_LEFT).str_pad($b, 2, '0',
            STR_PAD_LEFT);
    }

    /**
     * @throws Exception
     */
    public static function validateRgb($r, $g, $b): void
    {
        foreach ([$r, $g, $b] as $rgb) {
            if (! is_numeric($rgb)) {
                throw new Exception('RGB values must be numeric.');
            }
            if ($rgb < 0 || $rgb > 255) {
                throw new Exception('RGB values must be between 0 and 255.');
            }
        }
    }

    public static function hexColor(string $name): ?string
    {
        $color = ltrim($name, '#');

        if (preg_match('/^([a-fA-F0-9]{3}){1,2}$/', $color)) {
            return $name;
        }

        return null;
    }

    public static function validColor(string $color): bool
    {
        return in_array($color, self::getColorNames());
    }

    public static function validCondition(string $condition): bool
    {
        return in_array($condition, self::getColorConditions());
    }

    public static function validPanelName(string $panel): bool
    {
        return in_array($panel, self::getPanelNames());
    }

    public static function getColorNames(): array
    {
        return array_keys(Color::all());
    }

    public static function getColorConditions(): array
    {
        return array_keys(ColorManager::getColors());
    }

    /**
     * This reads the Providers/Filament directory, enumerating the provider files found there.
     * It pulls a list of words extracted as "the first word of each file found", which should
     * correspond to the panel name.
     */
    public static function getPanelNames(): Collection
    {
        $directoryPath = app_path('Providers/Filament');
        $firstWords    = collect([]);

        if (is_dir($directoryPath)) {
            if ($dh = opendir($directoryPath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                        $splitWords = preg_split('/(?=[A-Z])/', $file, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($splitWords) > 0) {
                            // Convert the first word to lowercase and add to the list
                            $firstWords->add(strtolower($splitWords[0]));
                        }
                    }
                }
                closedir($dh);

            }
        }

        return $firstWords;
    }
}