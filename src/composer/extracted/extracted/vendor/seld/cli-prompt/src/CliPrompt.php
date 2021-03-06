<?php


namespace Seld\CliPrompt;

class CliPrompt
{


    public static function hiddenPrompt($allowFallback = false)
    {

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {

            $exe = __DIR__ . '\\..\\res\\hiddeninput.exe';


            if ('phar:' === substr(__FILE__, 0, 5)) {
                $tmpExe = sys_get_temp_dir() . '/hiddeninput.exe';


                $source = fopen($exe, 'r');
                $target = fopen($tmpExe, 'w+');
                stream_copy_to_stream($source, $target);
                fclose($source);
                fclose($target);
                unset($source, $target);

                $exe = $tmpExe;
            }

            $output = shell_exec($exe);


            if (isset($tmpExe)) {
                unlink($tmpExe);
            }

            if ($output !== null) {

                echo PHP_EOL;

                return self::trimAnswer($output);
            }
        }

        if (file_exists('/usr/bin/env')) {

            $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";
            foreach (array('bash', 'zsh', 'ksh', 'csh', 'sh') as $sh) {
                if ('OK' === rtrim(shell_exec(sprintf($test, $sh)))) {
                    $shell = $sh;
                    break;
                }
            }

            if (isset($shell)) {
                $readCmd = ($shell === 'csh') ? 'set mypassword = $<' : 'read -r mypassword';
                $command = sprintf("/usr/bin/env %s -c 'stty -echo; %s; stty echo; echo \$mypassword'", $shell, $readCmd);
                $output = shell_exec($command);

                if ($output !== null) {

                    echo PHP_EOL;

                    return self::trimAnswer($output);
                }
            }
        }


        if (!$allowFallback) {
            throw new \RuntimeException('Could not prompt for input in a secure fashion, aborting');
        }

        return self::prompt();
    }

    private static function trimAnswer($str)
    {
        return preg_replace('{\r?\n$}D', '', $str);
    }

    public static function prompt()
    {
        $stdin = fopen('php://stdin', 'r');
        $answer = self::trimAnswer(fgets($stdin, 4096));
        fclose($stdin);

        return $answer;
    }
}
