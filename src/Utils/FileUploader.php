<?php

namespace App\Utils;


use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, User $user = null)
    {
        if($user){
            $fileName = $user->getFirstname().'-'.uniqid().'.'.$file->guessExtension();
            //suppression de l'ancienne image
            $fs = new Filesystem();
            $fs->remove($this->getTargetDirectory() . "/" . $user->getPhoto());
        }else{
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        }

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            return false;
        }



        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}