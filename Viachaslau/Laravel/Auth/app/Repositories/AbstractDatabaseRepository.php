<?php

namespace App\Repositories\Database;

use App\Annotations\Repositories\EntityRepository;
use App\Dto\Request\Common\TableInputDto;
use App\Dto\Response\Common\TableMetaResponse;
use App\Dto\Response\Common\TableResponse;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use LaravelDoctrine\ORM\Facades\EntityManager;

class AbstractDatabaseRepository
{
    private $objectRepository;

    public function __construct()
    {
        $reflectionObject = new \ReflectionObject($this);
        $reader = new AnnotationReader();

        /** @var EntityRepository $annotation */
        $annotation = $reader->getClassAnnotation($reflectionObject, EntityRepository::class);

        $this->objectRepository = EntityManager::getRepository($annotation->getEntity());
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    public function getObjectRepository(): \Doctrine\Persistence\ObjectRepository
    {
        return $this->objectRepository;
    }

    public function findAll()
    {
        return $this->getObjectRepository()->findAll();
    }

    public function remove($model)
    {
        EntityManager::remove($model);
        EntityManager::flush();
    }

    /**
     * Ищет записи с учётом пагинации и фильтров
     * использует метод getFilterQuery для фильтрации
     * @param TableInputDto $dto
     * @return TableResponse
     */
    public function findWithPagination(TableInputDto $dto): TableResponse
    {
        return $this->paginate($dto->getPage(), $dto->getPerPage(), $this->getFilterQuery($dto->getFilters()));
    }

    /**
     * Возвращает QueryBuilder с применёнными фильтрами/сортировками
     * Необходимо переопределить для добавления фильтров
     * @param array $filters
     * @return QueryBuilder
     */
    protected function getFilterQuery(array $filters): QueryBuilder
    {
        return $this->getObjectRepository()->createQueryBuilder('entity');
    }

    /**
     * Возвращает результат пагинации QueryBuilder
     * @param int $page
     * @param int $perPage
     * @param QueryBuilder $builder
     * @return TableResponse
     */
    protected function paginate(int $page, int $perPage, QueryBuilder $builder): TableResponse
    {
        $query = $builder->getQuery()->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);
        $paginator = new Paginator($query);

        return new TableResponse(iterator_to_array($paginator->getIterator()), new TableMetaResponse($page, $perPage, $paginator->count()));
    }

    public function clear(){
        EntityManager::createQueryBuilder()->delete($this->objectRepository->getClassName())->getQuery()->execute();
    }
}
