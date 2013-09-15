<?php

session_start();
error_reporting(E_ALL ^E_NOTICE);
require_once('../vendor/autoload.php');
require('./GitHandler.php');
require('./Book.php');
use Symfony\Component\Process\Process;
use SkrivEditor\Book;
use SkrivEditor\GitHandler;

$book = new Book();

// creation of the renderer object
$renderer = \Skriv\Markup\Renderer::factory();

/*
 * Ajax handler
 */
switch($_POST["action"]) {
    case "init":
        $output = "Now editing ".$book->getCurrentPage();
        $expireNum = time() - 3600;
        if (!isset($_SESSION["initCheck"])) {
            $_SESSION["initCheck"] = time();
        }
        // if the last time check is more than one day
        if ($_SESSION["initCheck"] <= $expireNum) {
            $gHdl = new gitHandler();
            $pullStatus = $gHdl->getPullStatus();
            $_SESSION["initCheck"] = time();
            /*
             * Deal with git errors
             */
            if ($pullStatus < 1) {
                $output = msg($gHdl->shortMessage, true);
            }
        }

        echo $output;

        break;
    case "push":
        build($book);
        $gHdl = new gitHandler();
        $pullStatus = $gHdl->getPullStatus();
        /*
         * Deal with git errors
         */
        if ($pullStatus < 1) {
            $output = msg($gHdl->shortMessage, true);
            echo $output;
            die();
        }

        $cmd[] = "git add ../.";
        $cmd[] = "git commit -m'Auto commit from doc editor'";
        $cmd[] = "git push origin master";
        $cmd[] = "git checkout gh-pages";
        $html = file_get_contents("../html/".$book->getLanguage()."/index.html");

        $res = shell_exec(implode(";", $cmd));

        file_put_contents("../html/".$book->getLanguage()."/index.html", $html);
        $cmd = array();
        $cmd[] = "git add ../html/. ";
        $cmd[] = "git commit -m'Auto commit from doc editor'";
        $cmd[] = "git push --force origin gh-pages";
        $cmd[] = "git checkout master";
        $status = shell_exec(implode(";", $cmd));
        echo "Html pushed to Github pages ";
        break;
    case "convert":
        echo $renderer->render($_POST["text"]);
        break;
    case "shutdown":
        $pid = shell_exec("ps ax | grep 'php -S localhost:8096' | grep -v grep");
        $pid = trim($pid);
        $pid = explode(" ", $pid);
        shell_exec("kill ".$pid[0]);
        break;
    case "setlg":
        $book->setLanguage($_POST["language"]);
        break;
    case "loadPage":
        echo file_get_contents("../".$book->getLanguage()."/".$book->getCurrentPage());
        break;
    case "loadConverted":
        echo $renderer->render(file_get_contents("../".$book->getLanguage()."/".$book->getCurrentPage()));
        break;
    case "save":
        file_put_contents("../".$book->getLanguage()."/".$book->getCurrentPage(), $_POST["text"]);
        echo "Content saved into ".$book->getLanguage()."/".$book->getCurrentPage();
        break;
    case "build":
        build($book);
        echo "Files generated into directory html/".$book->getLanguage()."/";
        break;
    case "prev":
        $book->moveBw();
        break;
    case "next":
        $book->moveFw();
        break;
    case "add":
        $book->addPage();
        break;
    case "del":
        $book->delPage();
        break;
}

function build($book)
{
    $language = $book->getLanguage();
    $toc = "";
    foreach ($book->getPages() as $page) {
        $renderer = \Skriv\Markup\Renderer::factory();
        $html[$page] = file_get_contents("../html/".$language."/tpl.htm");
        $html[$page] = str_replace("#{doc}#", $renderer->render(file_get_contents("../".$language."/".$page)), $html[$page]);
        $toc .= preg_replace("`a href=\"#([^\"]*)\"`","a href=\"".getHtmlPageName($page)."#\\1\"",$renderer->getToc());

    }

    foreach ($html as $skrivPage => $htmlPage) {
        $htmlPage = str_replace("#{toc}#", $toc, $htmlPage);
        file_put_contents("../html/".$language."/".getHtmlPageName($skrivPage),$htmlPage);
    }
}

function getHtmlPageName ($skrivPageName)
{
    return str_replace(".skriv", ".html", $skrivPageName);
}
function msg($text,$textarea = false)
{
    if ($textarea) {
        return "<textarea>".$text."</textarea>";
    } else {
        return $text;
    }
}
