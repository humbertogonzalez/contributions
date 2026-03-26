<?php

namespace Redegal\Middleware\Model\Repository;

interface RepositoryInterface
{

    public function find($id);

    public function findAll();

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);

    public function findOneBy(array $criteria);
}
