<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> BigFootDoc project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace SkrivEditor;

class GitHandler
{
    public $error;
    public $opt;
    public $shortMessage;

    public function getPullStatus ($branch = "master")
    {
        $status = 1;

        shell_exec("git checkout master");
        $process = "git pull origin ".$branch;
        $process = new Process($process);
        $process->run(function ($type, $buffer) {
            if ('err' === $type) {
                $this->error[] = $buffer;
            } else {
                $this->opt[] = $buffer;
            }
        });

        /*
         * Deal with git errors
         */
        if ($this->in_array_match("`Your local changes to the following files would be overwritten by merge`", $this->error)) {
            $this->shortMessage = "Update impossible, you have to commit or stash you local file, git said:\n".implode("", $this->error);
            $status = -1;
        } elseif ($this->in_array_match("`Automatic merge failed;`", $this->opt)) {
            $this->shortMessage = "Some conflicts have to be fixed, reload this page to see in the editor or go to you terminal";
            $status = -2;
        } elseif ($this->in_array_match("`Pull is not possible because you have unmerged files`", $this->error)) {
            $this->shortMessage = "You have unmerged files, you can correct directly in this editor but you have to add and commit manually,  git said:\n".implode("", $this->error);
            $status = -3;
        }

        return $status;

    }

    private function in_array_match($regex, $array)
    {
        if (!is_array($array)) {
            trigger_error('Argument 2 must be array');
        }
        foreach ($array as $v) {
            $match = preg_match($regex, $v);
            if ($match === 1) {
                return true;
            }
        }

        return false;
    }
}