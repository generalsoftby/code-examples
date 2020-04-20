<?php

namespace App\Services;

use App\Contracts\Deferred;
use App\Models;
use App\Repositories\DeferredRepository;

class SessionDeferred implements Deferred
{
    private $items = [];
    private $deferred;
    private $deferred_repository;
    private $token;

    public $cookie_token_key;

    public function __construct(){
        $this->deferred_repository = app(DeferredRepository::className());
        $this->cookie_token_key = 'deferred_token';
        $this->_getDeferred();
    }

    private function _getDeferred(){
        $this->token = $this->_getToken();

        if(!$this->token){
            $this->deferred = $this->_addDeferred();
        } else if (\Auth::user()) {
            $user_deferred = $this->deferred_repository->getUserDeferred();
            if(!$user_deferred){
                $this->deferred = $this->deferred_repository->getDeferred($this->token);
                $this->deferred_repository->updateDeferredUser($this->deferred);
            } else {
                $this->deferred = $user_deferred;
                $this->_store($user_deferred->token);
            }
        } else {
            $this->deferred = $this->deferred_repository->getDeferred($this->token);
            if (!$this->deferred) {
                $this->deferred = $this->_addDeferred();
            }
        }

        $this->items = $this->deferred_repository->getDeferredModels($this->deferred);
    }

    private function _addDeferred()
    {
        $deferred = $this->deferred_repository->addDeferred();
        $this->_store($deferred->token);

        return $deferred;
    }

    private function _getToken(){
        return \Cookie::get($this->cookie_token_key);
    }

    public function add(Models\Model $item){
        $this->items[$item->model_id] = [
            'item' => $item
        ];
        $this->deferred_repository->addDeferredModel($this->deferred, $item);
    }

    public function remove(Models\Model $item) {
        unset($this->items[$item->model_id]);
        $this->deferred_repository->deleteDeferredModel($this->deferred, $item);
    }

    public function getModels() {
        return $this->items;
    }

    public function getCount() {
        return count($this->items);
    }

    private function _store($token) {
        $this->token = $token;
        \Cookie::queue($this->cookie_token_key, $token, 365*24*60);
    }
}
