<?php

namespace NeoP\Log;

use NeoP\DI\Annotation\Mapping\Depend;
use NeoP\Log\Style;
use NeoP\Process\Processor;
use NeoP\Application;
use NeoP\Stdlib\Dir;

/**
 * @Depend()
 * @var Log
 */
class Log extends Style
{

    // 表格
    // ─│
    // ┌┬┐
    // ├┼┤
    // └┴┘
    const DELIMITER = [
        0 => [
            "start" => "[",
            "stop" => "]",
        ],
        1 => [
            "start" => "{",
            "stop" => "}",
        ]
    ];

    public static function info($data = "")
    {
        $type = __FUNCTION__;
        if (is_array($data) || is_object($data)) {
            self::formatObject($data, function (string $string = '') use ($type) {
                self::stdout('> [Info][' . date('Y-m-d H:i:s') . ']: ' . $string, 0, self::MODE_DEFAULT, '', '', $type);
            });
        } else {
            self::stdout('> [Info][' . date('Y-m-d H:i:s') . ']: ' . self::formatToString($data), 0, self::MODE_DEFAULT, '', '', $type);
        }
    }

    public static function debug($data = "")
    {
        $type = __FUNCTION__;
        if (\is_array($data) || is_object($data)) {
            self::formatObject($data, function (string $string = '') use ($type) {
                self::stdout('> [Debug][' . date('Y-m-d H:i:s') . ']: ' . $string, 0, self::MODE_DEFAULT, self::FG_WHITE, '', $type);
            });
        } else {
            self::stdout('> [Debug][' . date('Y-m-d H:i:s') . ']: ' . self::formatToString($data), 0, self::MODE_DEFAULT, self::FG_WHITE, '', $type);
        }
    }

    public static function warning($data = "")
    {
        $type = __FUNCTION__;
        if (\is_array($data) || is_object($data)) {
            self::formatObject($data, function (string $string = '') use ($type) {
                self::stdout('> [Warning][' . date('Y-m-d H:i:s') . ']: ' . $string, 0, self::MODE_DEFAULT, self::FG_CYAN, '', $type);
            });
        } else {
            self::stdout('> [Warning][' . date('Y-m-d H:i:s') . ']: ' . self::formatToString($data), 0, self::MODE_DEFAULT, self::FG_CYAN, '', $type);
        }
    }

    public static function error($data = "")
    {
        $type = __FUNCTION__;
        if (\is_array($data) || is_object($data)) {
            self::formatObject($data, function (string $string = '') use ($type) {
                self::stdout('> [Error][' . date('Y-m-d H:i:s') . ']: ' . $string, 0, self::MODE_DEFAULT, self::FG_RED, '', $type);
            });
        } else {
            self::stdout('> [Error][' . date('Y-m-d H:i:s') . ']: ' . self::formatToString($data), 0, self::MODE_DEFAULT, self::FG_RED, '', $type);
        }
    }

    public static function stdout($target, int $tabNum = 0, string $mode = Log::MODE_DEFAULT, string $fg = '', string $bg = '', string $type = 'info'): void
    {
        $out = Style::sprintf($target, $tabNum, $mode, $fg, $bg) . PHP_EOL;
        if (Processor::$isDaemon) {
            $filename = service('server.name', Application::$service);
            $tmpPath = service('server.tmp', 'runtime');
            $tmpPath = Dir::joinExecPath($tmpPath);
            $filename = $tmpPath . $filename . '.' . $type . '.log';
            Dir::pushFile($filename, (string) $out);
        } else {
            echo $out;
        }
    }

    protected static function formatToString($data)
    {
        switch (true) {
            case is_string($data):
                return $data;
                break;
            case is_int($data) || is_float($data):
                return (string) $data;
                break;
            case is_bool($data):
                return $data ? 'true' : 'false';
                break;
            default:
                return $data;
                break;
        }
    }

    protected static function formatObject($data, callable $stdout, int $tabNum = 0)
    {

        $tab = '';
        $delimiterStart = '[';
        $delimiterStop = ']';


        if (is_array($data)) {
            $delimiterStart = self::DELIMITER[0]['start'];
            $delimiterStop = self::DELIMITER[0]['stop'];
        } else {
            $delimiterStart = self::DELIMITER[1]['start'];
            $delimiterStop = self::DELIMITER[1]['stop'];
        }
        if ($tabNum == 0) {
            $stdout("|--------------------------------------------------|");
            $stdout("|-- Tip: Object cannot print private attributes. --|");
            $stdout("|-------- Print array and object log start --------|");
            $stdout("|--------------------------------------------------|");
        }

        $stdout("| " . str_pad($tab, $tabNum, "\t") . $delimiterStart, $tabNum);
        $tabNum += 1;
        foreach ($data as $key => $value) {
            $keySymbol = is_string($key) ? "\"" : "";
            switch (true) {
                case is_null($value):
                    $stdout("| " . str_pad($tab, $tabNum, "\t") . $keySymbol . $key  . $keySymbol . "\t=>\tNULL,", $tabNum);
                    break;
                case is_string($value) || is_numeric($value) || is_bool($value):
                    $valueType = is_string($value) ? "\"" : "";
                    is_bool($value) ? $value == TRUE ? $value = "true" :  $value = "false" : NULL;
                    $stdout("| " . str_pad($tab, $tabNum, "\t") . $keySymbol . $key  . $keySymbol . "\t=>\t" . $valueType . $value . $valueType . ",", $tabNum);
                    break;

                case is_array($data) || is_object($data):
                    if (is_callable($value)) {
                        $stdout("| " . str_pad($tab, $tabNum, "\t") . $keySymbol . $key  . $keySymbol . "\t=>\tfunction(){},", $tabNum);
                    } else {
                        $stdout("| " . str_pad($tab, $tabNum, "\t") . $keySymbol . $key  . $keySymbol . "\t=>\t", $tabNum);
                        self::formatObject($value, $stdout, $tabNum);
                    }
                    break;
            }
        }
        $tabNum -= 1;
        $stdout("| " . str_pad($tab, $tabNum, "\t") . $delimiterStop . ($tabNum == 0 ? "" : ","), $tabNum);

        if ($tabNum == 0) {
            $stdout("|--------------------------------------------------|");
            $stdout("|-------- Print array and object log stop  --------|");
            $stdout("|--------------------------------------------------|");
        }
    }

    protected static function get_variable_name(&$var, $scope)
    {

        $tmp = $var;

        $var = 'tmp_value_' . mt_rand();
        $name = array_search($var, $scope, true);

        $var = $tmp;
        return $name;
    }
}
