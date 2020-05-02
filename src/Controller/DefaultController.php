<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class DefaultController extends AbstractController
{
    /**
     * @Route("/refresh", name="refresh")
     */
    public function refreshAction(Request $request, SessionInterface $session)
    {
        $session->set('page', 0);

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/get/news/{index}", name="getnews")
     */
    public function getnewsAction($index, Request $request, SessionInterface $session)
    {
        $session->set('page', $index);

        $newsId = $index;

        $latest_date = null;
        $all_news = [];
        
        $return = $this->sunstarNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->absNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->cnnNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->tv5News($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $news = $all_news;

        return $this->json(['allnews' => $news]);
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, SessionInterface $session)
    {
        $newsId = $request->query->get('news');

        if ($newsId) {
            $newsId = $newsId;
        }
        else {
            $newsId = $session->get('page');

            if (!$newsId) {
                $newsId = 0;
            }
        }

        $latest_date = null;
        $all_news = [];
        
        $return = $this->sunstarNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->absNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->cnnNews($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $return = $this->tv5News($all_news, $latest_date);
        $all_news = $return['all_news'];
        $latest_date = $return['latest_date'];

        $tracker = $this->trackerByLocation();

        if ($newsId > (count($all_news) - 1)) {
            $newsId = 0;
        }

        $session->set('page', $newsId);

        return $this->render('base.html.twig',[
            'allnews' => $all_news,
            'id' => $newsId,
            'doh' => $this->dohUpdate(),
	        'tracker' => $tracker['return'],
	        'total' => $tracker['total'],
	        'confirmed' => $this->trackerConfirmed(),
	        'puis' => $this->trackerPUI(),
	        'pums' => $this->trackerPUM(),
	        'recovered' => $this->trackerRecovered(),
	        'deaths' => $this->trackerDeaths(),
        ]);
    }

    private function sunstarNews($all_news, $latest_date) {
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
                    'video' => '',
                    'content' => substr($data[4], 0, 500) . '...',
                    'artImg' => $data[5],
                    'website' => 'www.sunstar.com.ph',
                ];
            }
        }

        foreach ($return as $news) {
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

        return [
            'all_news' => $all_news,
            'latest_date' => $latest_date
        ];
    }

    private function absNews($all_news, $latest_date) {
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
	    $title = $data[1];
	    $title =  preg_replace("/827 dead:/", ", 827 dead:", $title);
                $return[] = [
                    'datetime' => $datetime->format('F j Y'),
                    'title' => $title,
                    'href' => $data[2],
                    'img' => 'images/abs.jpg',
                    'name' => 'ABS-CBN News',
                    'video' => $data[3],
                    'content' => substr($data[4], 0, 500) . '...',
                    'artImg' => $data[5],
                    'website' => 'news.abs-cbn.com',
                ];
            }
        }

        foreach ($return as $news) {
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

        return [
            'all_news' => $all_news,
            'latest_date' => $latest_date
        ];
    }

    private function cnnNews($all_news, $latest_date) {
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
                $datetime = preg_replace('/^.*lifestyle\//', '', $datetime);
                $datetime = preg_replace('/\/\D.*$/', '', $datetime);
                $datetime = \DateTime::createFromFormat('Y/n/j', $datetime);

                if (preg_match('/LIVE UPDATES/', $data[0])) {
                    continue;
                }

                $return[] = [
                    'datetime' => $datetime ? $datetime->format('F j Y') : null,
                    'title' => $data[0],
                    'href' => $data[1],
                    'img' => 'images/cnn.png',
                    'name' => 'CNN Philippines',
                    'video' => $data[2],
                    'content' => substr($data[3], 0, 500) . '...',
                    'artImg' => $data[4],
                    'website' => 'cnnphilippines.com',
                ];
            }
        }

        foreach ($return as $news) {
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

        return [
            'all_news' => $all_news,
            'latest_date' => $latest_date
        ];
    }

    private function tv5News($all_news, $latest_date) {
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
                    'video' => '',
                    'content' => substr($data[4], 0, 500) . '...',
                    'artImg' => '',
                    'website' => 'news.tv5.com.ph',
                ];
            }
        }

        foreach ($return as $news) {
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

        return [
            'all_news' => $all_news,
            'latest_date' => $latest_date
        ];
    }

    private function dohUpdate() {
        if (!file_exists($this->getFileDir().'doh.csv')) {
            return null;
        }

        $csv = file_get_contents($this->getFileDir().'doh.csv');
        $data = explode(",", $csv);

        if (isset($data[0])) {
            if ($data[0] == 'image') {
                $csv = preg_replace("/image,/","",$csv);
                return [
                    'image' => $csv
                ];
            }
            else {
                return [
                    'image' => null,
                    'date' => $data[0],
                    'confirmed' => $data[1],
                    'negative' => $data[2],
                    'pending' => $data[3]
                ];
            }
        }
    }

    private function trackerByLocation() {
	try {
        $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/PH_masterlist/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&groupByFieldsForStatistics=residence&orderByFields=value%20desc&outStatistics=%5B%7B%22statisticType%22%3A%22count%22%2C%22onStatisticField%22%3A%22FID%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&outSR=102100&cacheHint=true'; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result, true);

        $return = [];
	$total = 0;
	    if (isset($json['features'])) {
        foreach ($json['features'] as $data) {
            $att = $data['attributes'];
            $count = $att['value'];
            $residence = $att['residence'];

            $return[] = [
                'residence' => $residence,
                'count' => $count
            ];
	    $total += $count;
        }
        }

	return [
	    'return' => $return,
	    'total' => $total
	];

	}
	catch (Exception $e) {
	    return [];
	}
    }

    private function trackerConfirmed() {
        try {
            $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/slide_fig/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22confirmed%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            if (isset($json['features'])) {
                return $json['features'][0]['attributes']['value'];
            }
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function trackerPUI() {
        try {
            $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/slide_fig/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22PUIs%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            if (isset($json['features'])) {
                return $json['features'][0]['attributes']['value'];
            }
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function trackerPUM() {
        try {
            $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/slide_fig/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22PUMs%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            if (isset($json['features'])) {
                return $json['features'][0]['attributes']['value'];
            }
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function trackerRecovered() {
        try {
            $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/slide_fig/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=[{%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22recovered%22%2C%22outStatisticFieldName%22%3A%22value%22}]&cacheHint=true';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            if (isset($json['features'])) {
                return $json['features'][0]['attributes']['value'];
            }
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function trackerDeaths() {
        try {
            $url = 'https://services5.arcgis.com/mnYJ21GiFTR97WFg/arcgis/rest/services/slide_fig/FeatureServer/0/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22deaths%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            if (isset($json['features'])) {
                return $json['features'][0]['attributes']['value'];
            }
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function getFileDir() {
	return '/home/ubuntu/newslab/csvs/';
    }

    /**
     * @Route("/tracker", name="tracker")
     */
    public function trackerAction(Request $request, SessionInterface $session)
    {
        return $this->render('tracker.html.twig');
    }
}
