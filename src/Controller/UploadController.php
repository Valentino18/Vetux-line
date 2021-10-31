<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class UploadController extends AbstractController
{
    /**
     * @Route("/upload", name="upload")
     */
    public function index(Request $request): Response
    {

        $role=$this->getUser()->getRoles();
        $hasAccess=$this->isGranted("ROLE_GESTION");
        if  ($hasAccess){
            return $this->render('upload/index.html.twig', [
                'controller_name' => 'UploadController',
            ]);
        }
        else{
            return $this->render('notallow.html.twig',['role'=>$role[0]]);
        }
    }

    /**
     * @Route("/doUpload", name="do-upload")
     * @param Request $request
     * @param string $uploadDir
     * @param FileUploader $uploader
     * @param LoggerInterface $logger
     * @return Response
     */
    public function upload(Request $request, string $uploadDir,
                          FileUploader $uploader, LoggerInterface $logger): Response
    {
        $token = $request->get("token");

        if (!$this->isCsrfTokenValid('upload', $token))
        {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $file1 = $request->files->get('myfile1');
        $file2 = $request->files->get('myfile2');

        if (empty($file1)&& empty($file2))
        {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $filename = 'file1.csv';
        $uploader->upload($uploadDir, $file1, $filename);

        $filename = 'file2.csv';
        $uploader->upload($uploadDir, $file2, $filename);

        return $this->redirectToRoute('readcsv');
    }
}
