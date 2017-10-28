<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    private $filename = "";
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/download/{key}", name="download_single_mp3")
     */
    public function downloadSingleMp3Action($key)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix('youtube-dl');
        $url = 'https://www.youtube.com/watch?v=' . $key;
        $fname = '/tmp/%(title)s.%(ext)s';
        $args = array(
            '--newline',
            '--output',
            $fname,
            '-f',
            'm4a',
            '-x',
            '--',
            $url
        );

        $process = $builder
            ->setArguments($args)
            ->getProcess();
        $process->setTimeout(3600);
        try {
            $process->run(function ($type, $buffer) {
                if (preg_match('/Destination: (.*)/', $buffer, $matches)) {
                    $pieces = explode('/', $matches[1]);
                    $this->filename = end($pieces);
                }
            });
        } catch (\Exception $e) {
            echo $e;
        }
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
        $songPath = '/tmp/' . $this->filename;
        $response = $this->file($songPath);
        $response->deleteFileAfterSend(true);
        return $response;
    }
}
