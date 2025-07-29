<?php
namespace Mini\Controller;
use Mini\Model\Song;
class HomeController
{
    public function index()
    {
    $Song = new Song();
    // Получаем только 5 последних песен
    $songs = array_slice($Song->getAllSongs(), 0, 5);
    $amount_of_songs = $Song->getAmountOfSongs();
        // load views
        require APP . 'view/_templates/header.php';
        require APP . 'view/home/index.php';
        require APP . 'view/_templates/footer.php';
    }
}
