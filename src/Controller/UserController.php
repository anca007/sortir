<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UploadFileType;
use App\Form\UserType;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class   UserController extends AbstractController
{
    #[Route(path: '/participant/detail/{id}', name: 'user_detail')]
    public function detail($id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        return $this->render('user/detail.html.twig', [
            'user' => $user
        ]);

    }

    #[Route(path: '/participant/edit', name: 'user_edit')]
    public function edit(EntityManagerInterface       $entityManager,
                         Request                      $request,
                         UserPasswordHasherInterface  $encoder,
                         FileUploader                 $fileUploader): Response
    {

        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getData();
            $photo = $form->get('photo')->getData();

            if ($password) {
                $this->getUser()->setPassword($encoder->hashPassword($this->getUser(), $password));
            }


            if ($photo) {
                $fileName = $fileUploader->upload($photo, $this->getUser());

                if ($fileName) {
                    $this->getUser()->setPhoto($fileName);
                }
            }

            $entityManager->persist($this->getUser());
            $entityManager->flush();


            $this->addFlash("success", "Profil modifié avec succès !");
        }

        return $this->render('user/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }


    #[Route("/participant/import", name: "user_import")]
    public function import(Request $request, EntityManagerInterface $entityManager, CampusRepository $campusRepository)
    {

        $userForm = $this->createForm(UploadFileType::class);
        $userForm->handleRequest($request);


        if ($userForm->isSubmitted()) {

            /**
             * @var UploadedFile $file
             */
            $file = $userForm->get('users')->getData();
            $campus = $campusRepository->findOneBy(['name' => 'Rennes']);

            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {

                    $user = new User();
                    $user->setEmail($data[0])
                        ->setPassword($data[1])
                        ->setFirstname($data[2])
                        ->setLastname($data[3])
                        ->setPhone($data[4])
                        ->setPhoto($data[5])
                        ->setCampus($campus)
                        ->setRoles(['ROLE_USER'])
                    ;


                    $entityManager->persist($user);

                }
                $entityManager->flush();
            }
            $this->addFlash('success', 'Utilisateurs ajoutés !');

            return $this->redirectToRoute('user_import');
        }


        return $this->render("user/import.html.twig", [
            "uploadForm" => $userForm->createView()
        ]);
    }
}
