<?php

namespace Emmetog\Locker;

class Locker
{

    private $lockHandle;

    public function __construct($lockDirectory, $lockName)
    {
        $this->lock($lockDirectory, $lockName);
    }

    public function __destruct()
    {
        $this->unlock();
    }

    public function lock($lockDirectory, $lockName)
    {
        if (DIRECTORY_SEPARATOR != substr($lockDirectory,
                        strlen($lockDirectory) - 1))
        {
            $lockDirectory .= DIRECTORY_SEPARATOR;
        }

        if (!is_dir($lockDirectory) || !touch($lockDirectory . $lockName)) {
            throw new LockerUnableToCreateLockException();
        }

        $this->lockHandle = fopen($lockDirectory . $lockName, 'w');

        if (!flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            throw new LockerAlreadyLockedException();
        }

        ftruncate($this->lockHandle, 0); // truncate file
        //write something to just help debugging
        fwrite($this->lockHandle, "Locked\n");
        fflush($this->lockHandle);

//        echo "lock aquired on the file " . $lockDirectory . $lockName . PHP_EOL;
    }

    public function unlock()
    {
        flock($this->lockHandle, LOCK_UN);
        ftruncate($this->lockHandle, 0); // truncate file
        //write something to just help debugging
        fwrite($this->lockHandle, "Unlocked\n");
        fflush($this->lockHandle);
    }

}

class LockerException extends \Exception
{

}

class LockerAlreadyLockedException extends LockerException
{

}

class LockerUnableToCreateLockException extends LockerException
{

}
