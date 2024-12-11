<?php

namespace App\Service;

class ProgramDuration
{
    
    function calculate($seasons): string
    {
        //pour un ensemble de saisons, pour chaque saison, extrait les épisodes, et pour chaque épisode, aditionne la "duration"
        $time = 0;
        foreach($seasons as $season) {
            $episodes = $season->getEpisodes();
            foreach($episodes as $episode) {
                $time += $episode->getDuration();
            }
        }
        
        //crée deux dates, une qui commence en 1970 0:0:0:etc et l'autre pareil avec un temps en plus en sec, on fait la diff et convertit comme souhaité
        $time *= 60;
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$time");
        return $dtF->diff($dtT)->format('%d days, %h hour and %i minutes');
    }
}
