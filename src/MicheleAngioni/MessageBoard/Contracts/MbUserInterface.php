<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface MbUserInterface
{
    public function mbBans();

    public function mbLastView();

    public function mbPosts();

    public function mbRoles();

    public function getPrimaryId();

    public function getUsername();

    public function getLastViewDatetime();

    public function isBanned();

    public function getBan();

    public function hasMbRole($name);

    public function canMb($permission);
}
