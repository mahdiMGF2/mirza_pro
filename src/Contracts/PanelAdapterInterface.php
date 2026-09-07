<?php

interface PanelAdapterInterface
{
    /**
     * Return the normalized shape consumed by ManagePanel.
     */
    public function createUser(array $panel, string $username, int $expire, int $dataLimit, array $options = []): array;

    public function getUser(array $panel, string $username): array;

    public function deleteUser(array $panel, string $username): array;
}
