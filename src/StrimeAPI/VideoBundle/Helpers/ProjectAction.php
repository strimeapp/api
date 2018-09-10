<?php

namespace StrimeAPI\VideoBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\ImageBundle\Entity\Image;
use StrimeAPI\AudioBundle\Entity\Audio;

class ProjectAction {

    public $project;
    private $container;
    private $doctrine;
    protected $em;



    public function __construct(EntityManager $em, Container $container, $doctrine) {
        $this->em = $em;
        $this->container = $container;
        $this->doctrine = $doctrine;
    }



    /**
     * @return integer
     */
    public function countAssetsInProject()
    {
        // Define the variable
        $nb_videos_in_project = $nb_images_in_project = $nb_audios_in_project = 0;
        $nb_assets_in_project = 0;

        // Count the number of videos associated to this project
        $videos_in_project = $this->doctrine->getRepository('StrimeAPIVideoBundle:Video');

        $query = $videos_in_project->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.project = :project')
            ->setParameter('project', $this->project->getId())
            ->getQuery();

        $nb_videos_in_project = $query->getSingleScalarResult();


        // Count the number of images associated to this project
        $images_in_project = $this->doctrine->getRepository('StrimeAPIImageBundle:Image');

        $query = $images_in_project->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.project = :project')
            ->setParameter('project', $this->project->getId())
            ->getQuery();

        $nb_images_in_project = $query->getSingleScalarResult();


        // Count the number of audios associated to this project
        $audios_in_project = $this->doctrine->getRepository('StrimeAPIAudioBundle:Audio');

        $query = $audios_in_project->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.project = :project')
            ->setParameter('project', $this->project->getId())
            ->getQuery();

        $nb_audios_in_project = $query->getSingleScalarResult();

        // Set the final result
        $nb_assets_in_project = $nb_videos_in_project + $nb_images_in_project + $nb_audios_in_project;

        // Return the result
        return $nb_assets_in_project;
    }

}
