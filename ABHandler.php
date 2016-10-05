<?php

namespace App\Helpers;

use Session;

class ABHandler
{
    private static $experiments = ['expA', 'expB', 'expC', 'expD'];
    private static $goals = ['goal1', 'goal2', 'goal3'];
    private static $path = "../";

    private static function getFile()
    {
        $file = fopen(ABHandler::$path, "r");
        $filesize = filesize(ABHandler::$path);
        $file_content = "";
        if ($filesize != 0)
            $file_content = json_decode(fread($file, filesize(ABHandler::$path)), true);
        fclose($file);

        return $file_content;
    }

    private static function setFileContent($content)
    {
        file_put_contents(ABHandler::$path, json_encode($content));
    }

    private static function setCurrentExperiment()
    {
        $file = fopen(ABHandler::$path, "r");
        $filesize = filesize(ABHandler::$path);

        $experiments_stats = ABHandler::getFile();

        if (empty($experiments_stats))
        {
            array_walk(ABHandler::$experiments, function(&$value, &$key) use (&$experiments_stats) {

                $goals = ABHandler::$goals;
                $experiments_stats[$value] = 
                                        array_merge(['visitors' => 0], 
                                                array_combine($goals, array_fill(0, count($goals), 0)));
            });
        }

        $experiment = ABHandler::$experiments[mt_rand(0, count(ABHandler::$experiments)-1)];
        Session::set('experiment', $experiment);
        $experiments_stats[$experiment]['visitors']++;

        ABHandler::setFileContent($experiments_stats);
    }

    public static function getCurrentExperiment()
    {
        if (!Session::has('experiment'))
        {
            ABHandler::setCurrentExperiment();
        }

        return Session::get('experiment');
    }

    public static function reachedGoal($goal)
    {
        $experiments_stats = ABHandler::getFile();

        if(in_array($goal, array_keys($experiments_stats[ABHandler::getCurrentExperiment()])))
        {
            $experiments_stats[ABHandler::getCurrentExperiment()][$goal]++;
        }

        ABHandler::setFileContent($experiments_stats);
    }


}
