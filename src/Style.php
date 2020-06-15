<?php

namespace NeoP\Log;

use NeoP\Process\Processor;

class Style 
{
    // Foreground color
    public const DEFAULT = '';
    public const FG_BLACK = '30';
    public const FG_RED = '31';
    public const FG_GREEN = '32';
    public const FG_YELLOW = '33';
    public const FG_BLUE = '34';
    public const FG_FUCHSIA = '35';
    public const FG_CYAN = '36';
    public const FG_WHITE = '37';

    // Background color
    public const BG_BLACK = '40';
    public const BG_RED = '41';
    public const BG_GREEN = '42';
    public const BG_YELLOW = '43';
    public const BG_BLUE = '44';
    public const BG_FUCHSIA = '45';
    public const BG_CYAN = '46';
    public const BG_WHITE = '47';

    // 显示方式
    public const MODE_DEFAULT = '0';
    public const MODE_HIGHLIGHT = '1';
    public const MODE_UNDERLINE = '4';
    public const MODE_FLASHING = '5';
    public const MODE_REVERSE = '7';
    public const MODE_INVISIBLE = '8';

    // 界定符 
    public const DELIMITER_START = "\033[";
    public const DELIMITER_DEVISION = ';';
    public const DELIMITER_STOP = 'm';

    // tab
    public const DELIMITER_SPACE_NUM = 4;

    public static function sprintf(string $target, int $tabNum = 0, string $mode = Style::MODE_DEFAULT, string $fg = '', string $bg = ''): string
    {
        if (! Processor::$isDaemon) {
            $color = self::getColor($mode, $fg, $bg);
            $sprintf = [
                self::DELIMITER_START,
                $color,
                self::DELIMITER_STOP,
                "",
                $target,
                self::DELIMITER_START,
                self::MODE_DEFAULT,
                self::DELIMITER_STOP,
            ];
            return sprintf("%s%s%s%'\t{$tabNum}s%s%s%s%s", ...$sprintf);
        }
        return sprintf("%'\t{$tabNum}s", $target);
    }

    public static function getTab(int $tabNum = 0): string
    {
        $tab = "";
        for ($i = 0; $i > $tabNum; $i++) {
            $tab .= self::DELIMITER_TAB;
        }
        return $tab;
    }

    public static function getColor(string $mode = Style::MODE_DEFAULT, string $fg = '', string $bg = ''): string
    {
        $color = "";
        if ($fg != '') {
            $color .= $fg;
            if ($bg != '') {
                $color .= self::DELIMITER_DEVISION . $bg;
            }
            if ($mode != self::MODE_DEFAULT) {
                $color .= self::DELIMITER_DEVISION . $mode;
            }
        } else {
            $color .= $mode;
        }
        return $color;
    }
}