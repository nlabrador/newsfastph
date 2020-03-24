<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $sunstar = $this->sunstarNews();
        $abs = $this->absNews();
        $cnn = $this->cnnNews();
        $tv5 = $this->tv5News();

        return $this->render('base.html.twig',[
            'sunstar' => $sunstar,
            'abs' => $abs,
            'cnn' => $cnn,
            'tv5' => $tv5
        ]);
    }

    private function sunstarNews() {
        if (!file_exists($this->getFileDir().'sunstar.csv')) {
            return [];
        }

        $csv = file_get_contents($this->getFileDir().'sunstar.csv');

        $csvs = explode("\n", $csv);

        $return = [];
        foreach ($csvs as $news) {
            $data = explode(",", $news);

            if (isset($data[1])) {
                $return[] = [
                    'datetime' => $data[0],
                    'title' => $data[1],
                    'href' => $data[2]
                ];
            }
        }

        return $return;
    }

    private function absNews() {
        if (!file_exists($this->getFileDir().'abs.csv')) {
            return [];
        }

        $csv = file_get_contents($this->getFileDir().'abs.csv');

        $csvs = explode("\n", $csv);

        $return = [];
        foreach ($csvs as $news) {
            $data = explode(",", $news);
            $datetime = $data[0];
            $datetime = preg_replace("/\+.*$/", "", $datetime);
            $datetime = preg_replace("/T/", " ", $datetime);
            $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);

            if (isset($data[1])) {
                $return[] = [
                    'datetime' => $datetime->format('M j Y'),
                    'title' => $data[1],
                    'href' => $data[2]
                ];
            }
        }

        return $return;
    }

    private function cnnNews() {
        if (!file_exists($this->getFileDir().'cnn.csv')) {
            return [];
        }

        $csv = file_get_contents($this->getFileDir().'cnn.csv');

        $csvs = explode("\n", $csv);

        $return = [];
        foreach ($csvs as $news) {
            $data = explode(",", $news);

            if (isset($data[1])) {
                $return[] = [
                    'datetime' => '',
                    'title' => $data[0],
                    'href' => $data[1]
                ];
            }
        }

        return $return;
    }

    private function tv5News() {
        if (!file_exists($this->getFileDir().'news5.csv')) {
            return [];
        }

        $csv = file_get_contents($this->getFileDir().'news5.csv');

        $csvs = explode("\n", $csv);

        $return = [];
        foreach ($csvs as $news) {
            $data = explode(",", $news);
            $datetime = $data[0];
            $datetime = preg_replace("/ \d\d:.*$/", "", $datetime);

            if (isset($data[1])) {
                $return[] = [
                    'datetime' => $datetime,
                    'title' => $data[1],
                    'href' => $data[2]
                ];
            }
        }

        return $return;
    }

    private function getFileDir() {
        return '/Users/nlabrador/newslab/csvs/';
    }
}
