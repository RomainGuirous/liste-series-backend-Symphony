<?php

namespace App\Service;

class ProgramDuration
{
    function calculate($seasons): string
    {
        $time = 0;
        foreach($seasons as $season) {
            $episodes = $season->getEpisodes();
            foreach($episodes as $episode) {
                $time += $episode->getDuration();
            }
        }
        
        $time *= 60;
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$time");
        return $dtF->diff($dtT)->format('%d days, %h hour and %i minutes');
    }
}
