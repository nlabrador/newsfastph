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

        $latest_date = null;
        $all_news = [];

        foreach ($sunstar as $news) {
            if (!$latest_date) {
                $latest_date = $news['datetime'];

                $all_news[] = $news;
            }
            else {
                if ($news['datetime']) {
                    $ldatetime = \DateTime::createFromFormat('F j Y', $latest_date); 
                    $datetime = \DateTime::createFromFormat('F j Y', $news['datetime']);

                    if ($datetime > $ldatetime) {
                        $latest_date = $news['datetime'];

                        array_unshift($all_news, $news);
                    }
                    else {
                        $all_news[] = $news;
                    }
                }
                else {
                    $all_news[] = $news;
                }
            }
        }
        foreach ($tv5 as $news) {
            if (!$latest_date) {
                $latest_date = $news['datetime'];

                $all_news[] = $news;
            }
            else {
                if ($news['datetime']) {
                    $ldatetime = \DateTime::createFromFormat('F j Y', $latest_date); 
                    $datetime = \DateTime::createFromFormat('F j Y', $news['datetime']);

                    if ($datetime > $ldatetime) {
                        $latest_date = $news['datetime'];

                        array_unshift($all_news, $news);
                    }
                    else {
                        if ($datetime->format('F j Y') == $ldatetime->format('F j Y')) {
                            array_unshift($all_news, $news);
                        }
                        else {
                            $all_news[] = $news;
                        }
                    }
                }
                else {
                    $all_news[] = $news;
                }
            }
        }
        foreach ($abs as $news) {
            if (!$latest_date) {
                $latest_date = $news['datetime'];

                $all_news[] = $news;
            }
            else {
                if ($news['datetime']) {
                    $ldatetime = \DateTime::createFromFormat('F j Y', $latest_date); 
                    $datetime = \DateTime::createFromFormat('F j Y', $news['datetime']);

                    if ($datetime > $ldatetime) {
                        $latest_date = $news['datetime'];

                        array_unshift($all_news, $news);
                    }
                    else {
                        if ($datetime->format('F j Y') == $ldatetime->format('F j Y')) {
                            array_unshift($all_news, $news);
                        }
                        else {
                            $all_news[] = $news;
                        }
                    }
                }
                else {
                    $all_news[] = $news;
                }
            }
        }
        foreach ($cnn as $news) {
            if (!$latest_date) {
                $latest_date = $news['datetime'];

                $all_news[] = $news;
            }
            else {
                if ($news['datetime']) {
                    $ldatetime = \DateTime::createFromFormat('F j Y', $latest_date); 
                    $datetime = \DateTime::createFromFormat('F j Y', $news['datetime']);

                    if ($datetime > $ldatetime) {
                        $latest_date = $news['datetime'];

                        array_unshift($all_news, $news);
                    }
                    else {
                        if ($datetime->format('F j Y') == $ldatetime->format('F j Y')) {
                            array_unshift($all_news, $news);
                        }
                        else {
                            $all_news[] = $news;
                        }
                    }
                }
                else {
                    $all_news[] = $news;
                }
            }
        }

        return $this->render('base.html.twig',[
            'all_news' => $all_news,
            'doh' => $this->dohUpdate()
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
                    'href' => $data[2],
                    'img' => 'images/sunstar.png',
                    'name' => 'Sunstar Philippines',
                    'website' => 'www.sunstar.com.ph',
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
                    'datetime' => $datetime->format('F j Y'),
                    'title' => $data[1],
                    'href' => $data[2],
                    'img' => 'images/abs.jpg',
                    'name' => 'ABS-CBN News',
                    'website' => 'news.abs-cbn.com',
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
                $datetime = $data[1];
                $datetime = preg_replace('/^.*news\//', '', $datetime);
                $datetime = preg_replace('/^.*regional\//', '', $datetime);
                $datetime = preg_replace('/\/\D.*$/', '', $datetime);
                $datetime = \DateTime::createFromFormat('Y/n/j', $datetime);

                $return[] = [
                    'datetime' => $datetime->format('F j Y'),
                    'title' => $data[0],
                    'href' => $data[1],
                    'img' => 'images/cnn.png',
                    'name' => 'CNN Philippines',
                    'website' => 'cnnphilippines.com',
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
            $datetime = preg_replace("/\s+$/", "", $datetime);

            if (isset($data[1])) {
                $return[] = [
                    'datetime' => $datetime,
                    'title' => $data[1],
                    'href' => $data[2],
                    'img' => 'images/tv5.png',
                    'name' => 'tv5 News5',
                    'website' => 'news.tv5.com.ph',
                ];
            }
        }

        return $return;
    }

    private function dohUpdate() {
        if (!file_exists($this->getFileDir().'doh.csv')) {
            return null;
        }

        $csv = file_get_contents($this->getFileDir().'doh.csv');
        $data = explode(",", $csv);

        if (isset($data[0])) {
            return [
                'date' => $data[0],
                'confirmed' => $data[1],
                'negative' => $data[2],
                'pending' => $data[3]
            ];
        }
    }

    private function getFileDir() {
        return '/Users/nlabrador/newslab2/csvs/';
    }
}
