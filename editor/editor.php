<?php
session_start();
error_reporting(E_ALL ^E_NOTICE);
require_once('../vendor/autoload.php');
// creation of the renderer object
$renderer = \Skriv\Markup\Renderer::factory();

switch($_POST["action"]) {
    case "convert":
        echo $renderer->render($_POST["text"]);
        break;
    case "shutdown":
        $pid = shell_exec("ps ax | grep 'php -S localhost:8096' | grep -v grep | cut -d' ' -f1");
        shell_exec("kill ".$pid);
        break;
    case "setlg":
        $_SESSION["language"] = $_POST["language"];
        echo "Setted language in ".$_SESSION["language"];
        break;
    case "save":
        file_put_contents("../".$_SESSION["language"]."/doc.skriv",$_POST["text"]);
        echo "Content saved into directory ".$_SESSION["language"]."/";
        break;
    case "push":
        $cmd[] = "git add . >>/tmp/bfdoc.log";
        $cmd[] = "git commit -m'Auto commit from doc editor' >>/tmp/bfdoc.log";
        $cmd[] = "git push origin master >>/tmp/bfdoc.log";
        $cmd[] = "git checkout gh-pages >>/tmp/bfdoc.log";
        $cmd[] = "git add html/. >>/tmp/bfdoc.log";
        $cmd[] = "git commit -m'Auto commit from doc editor' >>/tmp/bfdoc.log";
        $cmd[] = "git push origin gh-pages >>/tmp/bfdoc.log";
        $cmd[] = "git checkout master >>/tmp/bfdoc.log";
        $res = shell_exec(implode(";",$cmd));
        echo "Html pushed to Github pages ".$res;
        file_put_contents("/tmp/bfdoc.log",$res,FILE_APPEND);
        break;
    case "build":
        $tpl = file_get_contents("../html/".$_SESSION["language"]."/tpl.htm");
        $tpl = str_replace("#{doc}#",$renderer->render(file_get_contents("../".$_SESSION["language"]."/doc.skriv")),$tpl);
        $tpl = str_replace("#{toc}#",$renderer->getToc(),$tpl);
        file_put_contents("../html/".$_SESSION["language"]."/index.html",$tpl);
        echo "Files generated into directory html/".$_SESSION["language"]."/";
        break;
}


