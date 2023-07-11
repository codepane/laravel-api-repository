<?php

namespace Codepane\LaravelAPIRepository\Repositories;

use Codepane\LaravelAPIRepository\Resources\PaginationCollection;
use Codepane\LaravelAPIRepository\Traits\APIResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    use APIResponse;

    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     *
     * General function to handle select cases
     *
     * @param select Array Pass array of columns to be selected
     * @param with String or Array of Relations to be selectd
     * @param withCount String or Array of Related count to be selected
     * @param join String or Array of table to be joined
     * eg $join = 'countries,countries.id,=,salary_ranges.country_id'
     * @param id return a single record
     * @param where key value array to add condition to query
     * @param order_by sorting on column name
     * @param order asc or desc
     * @param per_page if passed will return paginated response or all records
     *
     */
    public function getByParams($params = [], $onlyData = false)
    {

        try {
            $records = [];

            $query = $this->entity::whereRaw('1=1');
            if (isset($params['select'])) 
                $query->select($params['select']);

            if (isset($params['with'])) {
                $withes = Arr::wrap($params['with']);
                foreach ($withes as $with) {
                    $query->with($with);
                }
            }

            if (isset($params['withCount'])) {

                $withCounts = Arr::wrap($params['withCount']);
                foreach ($withCounts as $withCount) {
                    $query->withCount($withCount);
                }
            }

            if (isset($params['withSum'])) {
                foreach ($params['withSum'] as $key => $value) {
                    $query->withSum($key, $value);
                }
            }

            if (isset($params['whereHas'])) {
                $whereHas = Arr::wrap($params['whereHas']);
                foreach ($whereHas as $relation => $has) {
                    $query->whereHas($relation, function ($query) use ($has) {
                        foreach ($has as $key => $value) {
                            $query->where($key, $value);
                        }
                    });
                }
            }

            if (isset($params['orWhereHas'])) {
                $whereHas = Arr::wrap($params['orWhereHas']);
                foreach ($whereHas as $relation => $has) {
                    $query->orWhereHas($relation, function ($query) use ($has) {
                        foreach ($has as $key => $value) {
                            $query->where($key, $value);
                        }
                    });
                }
            }

            if (isset($params['whereHasQuery'])) {
                $whereHasQuery = Arr::wrap($params['whereHasQuery']);
                foreach ($whereHasQuery as $relation => $has) {
                    $query->whereHas($relation, $has);
                }
            }

            if (isset($params['whereHasNot'])) {
                $whereHas = Arr::wrap($params['whereHasNot']);
                foreach ($whereHas as $relation => $has) {
                    $query->whereHas($relation, function ($query) use ($has) {
                        foreach ($has as $key => $value) {
                            $query->where($key, '!=', $value);
                        }
                    });
                }
            }

            // add joins to main object
            if (isset($params['join'])) {
                $joins = Arr::wrap($params['join']);
                foreach ($joins as $join) {
                    $parts = explode(',', $join);
                    $query->join($parts[0], $parts[1], $parts[2], $parts[3]);
                }
            }

            // add joins to main object
            if (isset($params['leftjoin'])) {
                $joins = Arr::wrap($params['leftjoin']);
                foreach ($joins as $join) {
                    $parts = explode(',', $join);
                    $query->leftjoin($parts[0], $parts[1], $parts[2], $parts[3]);
                }
            }

            // return if single object is needed
            if (isset($params['id'])) 
                $query->where('id', $params['id']);

            if (isset($params['where'])) {
                foreach ($params['where'] as $key => $value) {
                    $query->where($key, $value);
                }
            }

            if (isset($params['orWhere'])) {
                foreach ($params['orWhere'] as $key => $value) {
                    $query->orWhere($key, $value);
                }
            }

            if (isset($params['whereRawQuery'])) {
                foreach ($params['whereRawQuery'] as $key => $subQuery) {
                    $query->where($subQuery);
                }
            }

            if (isset($params['whereNot'])) {
                foreach ($params['whereNot'] as $key => $value) {
                    $query->where($key, '!=', $value);
                }
            }

            if (isset($params['in'])) {
                foreach ($params['in'] as $key => $value) {
                    $query->whereIn($key, $value);
                }
            }

            if (isset($params['not_in'])) {
                foreach ($params['not_in'] as $key => $value) {
                    $query->whereNotIn($key, $value);
                }
            }

            if (isset($params['whereNull'])) {
                $query->whereNull($params['whereNull']);
            }

            if (isset($params['whereNotNull'])) {
                $query->whereNotNull($params['whereNotNull']);
            }

            if (isset($params['likeRaw'])) {
                foreach ($params['likeRaw'] as $key => $value) {
                    $query->WhereRaw($value);
                }
            }

            if (isset($params['like'])) {
                $query->where(function ($query) use ($params) {
                    foreach ($params['like'] as $key => $value) {
                        if ($key == 'whereRaw') {
                            $query->orWhereRaw($value);
                        } else {
                            $query->orwhere($key, 'like', $value);
                        }
                    }
                });
            }


            if (isset($params['date_range'])) {
                $index = 0;
                foreach ($params['date_range'] as $key => $value) {
                    if ($index == '1') {
                        $query->orWhereBetween($key, $value);
                    } else {
                        $query->whereBetween($key, $value);
                    }
                    $index++;
                }
            }

            if (isset($params['group_by'])) {
                $query->groupBy($params['group_by']);
            }

            if (isset($params['limit'])) {
                $query->limit($params['limit']);
            }

            if (isset($params['exists'])) {
                $record = $query->exists();

                return $record;
            }

            if (isset($params['first'])) {
                $record = $query->first();

                return $this->successHandler('Details has been fetch successfully!', $record, $onlyData);
            }

            if (isset($params['count'])) {
                $records = $query->count();
                return $records;
            }

            if (isset($params['distinct'])) {
                $records = $query->distinct();
                return $records;
            }

            if (isset($params['order_by'])) {

                $order = isset($params['order']) ? $params['order'] : 'asc';
                $query->orderBy($params['order_by'], $order);
            }

            if (isset($params['per_page']) && is_numeric($params['per_page'])) {
                $records = $query->paginate($params['per_page']);

                if (!$onlyData)
                    $records = new PaginationCollection($records);
            } else {
                $records = $query->get();
            }

            return $this->successHandler('Records has been fetch successfully!', $records, $onlyData);
        } catch (\Exception $e) {
            return $this->errorHandler($e, $onlyData);
        }
    }

    /**
     * get record by id
     * @param int $id - Record Id
     * @param bool $onlyData - Pass true if you don't want to details with api response
     * @return object|array
     */
    public function getById($id, $onlyData = false)
    {
        try {
            $record = $this->entity::find($id);

            if (!$record)
                return $this->errorHandler('Record does not exist', $onlyData, config('custom.api.http_codes.error.not_found'));

            return $this->successHandler('Details has been fetch successfully!', $record, $onlyData);
        } catch (\Exception $e) {
            return $this->errorHandler($e, $onlyData);
        }
    }

    /**
     * get the flag for record exist or not
     * @param int $id - Record I'd
     * @return bool
     */
    public function isRecordExists($id): bool
    {
        return $this->entity::whereId($id)->exists();
    }

    /**
     * Delete the record by id
     *
     * @param int $id - Record Id
     * @param bool $onlyData - Pass true if you don't want to details with api response
     * @return object|bool
     */
    public function delete($id, $onlyData = false)
    {
        abort_if(!is_numeric($id), config('laravel-api-repository.api.http_codes.error.internal_server'));

        DB::beginTransaction();
        try {
            $record = $this->entity->whereId($id)->first();

            if(!$record)
                return $this->errorHandler('Record does not exist!', false, config('laravel-api-repository.api.http_codes.error.not_found'));

            $record->delete();

            DB::commit();
            return $this->successHandler('Record has been deleted successfully!', true, $onlyData);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorHandler($e, $onlyData);
        }
    }

    /**
     * store the record in db
     * @param array $params
     * @param bool $onlyData
     * @param null $message
     * @return JsonResponse|array
     */
    public function save($params, $onlyData = false, $message = null)
    {
        DB::beginTransaction();
        try {
            $entity = $this->entity->updateOrCreate(
                ['id' => isset($params['id']) ? $params['id'] : null],
                $params
            );

            DB::commit();

            if (is_null($message) && isset($params['id']) && $params['id'])
                $message = 'Record has been updated successfully!';
            elseif (is_null($message))
                $message = 'Record has been added successfully!';

            return $this->successHandler($message, $entity, $onlyData);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorHandler($e, $onlyData);
        }
    }

    /**
     * handle the success response
     * @param string $message
     * @param mixed $data
     * @param bool $onlyData
     * @return array|object|JsonResponse
     */
    public function successHandler($message, $data, $onlyData = false)
    {
        if ($onlyData) return $data;

        return $this->success($message, $data);
    }

    /**
     * handle the error response
     * @param mixed $e
     * @param bool $onlyData
     * @return array|object|JsonResponse
     */
    public function errorHandler($e, $onlyData, $status = null)
    {
        $status = is_null($status) ? config('laravel-api-repository.api.http_codes.error.internal_server') : $status;

        if ($onlyData) return $e->getMessage();

        return $this->error(
            (is_string($e)) ? $e : $e->getMessage(),
            (!is_string($e) && $e->getCode() != 0) ? $e->getCode() : $status
        );
    }
}
