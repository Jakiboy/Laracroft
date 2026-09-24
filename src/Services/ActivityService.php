<?php

namespace Laracroft\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Laracroft\Resources\ActivityCollection;
use Laracroft\Resources\ActivityResource;

class ActivityService
{
    /**
     * @inheritdoc
     */
    public static function getAll(Request $request) : ActivityCollection
    {
        $items = (int)$request->query('items', 5);
        $search = $request->query('search');
        $sortField = $request->query('sField', 'created_at');
        $sortDirection = $request->query('sDir', 'desc');
        $filters = self::filters($request);

        $sortAllowed = [
            'created_at'  => 'activity_log.created_at',
            'description' => 'activity_log.description',
            'causer_id'   => 'activity_log.causer_id',
        ];

        $sortField = $sortAllowed[$sortField] ?? 'activity_log.created_at';
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        if ( $search ) {
            return self::search($search, $items, $sortField, $sortDirection, $filters);
        }

        $query = self::query()->orderBy($sortField, $sortDirection)->limit(100);
        self::applyFilters($query, $filters);

        return new ActivityCollection($query->paginate($items));
    }

    /**
     * @inheritdoc
     */
    public static function get(Model $activity) : ActivityResource
    {
        $activity->load('causer');

        return new ActivityResource($activity);
    }

    /**
     * @inheritdoc
     */
    public static function search(string $search, int $items = 5, string $sortField = 'activity_log.created_at', string $sortDirection = 'desc', array $filters = []) : ActivityCollection
    {
        try {
            $searchTerm = '%' . trim($search) . '%';
            $userTable = self::userModel()::query()->getModel()->getTable();
            $query = self::query()->where(function ($query) use ($searchTerm, $userTable) {
                $query->where('activity_log.description', 'ILIKE', $searchTerm)
                    ->orWhere("{$userTable}.first_name", 'ILIKE', $searchTerm)
                    ->orWhere("{$userTable}.last_name", 'ILIKE', $searchTerm)
                    ->orWhere("{$userTable}.email", 'ILIKE', $searchTerm);
            });

            self::applyFilters($query, $filters);

            return new ActivityCollection(
                $query->orderBy($sortField, $sortDirection)
                    ->select('activity_log.*')
                    ->limit(100)
                    ->paginate($items)
            );
        } catch (\Exception $exception) {
            Log::error('Activity search error: ' . $exception->getMessage());

            return new ActivityCollection(new LengthAwarePaginator([], 0, $items, 1));
        }
    }

    /**
     * @inheritdoc
     */
    public static function delete(Model $activity) : bool
    {
        try {
            $activity->delete();

            return true;
        } catch (\Exception $exception) {
            Log::error('Activity delete error: ' . $exception->getMessage(), [
                'activity_id' => $activity->id,
            ]);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    private static function query()
    {
        $activityModel = config('activitylog.activity_model');
        $userModel = self::userModel();
        $userTable = $userModel::query()->getModel()->getTable();

        return $activityModel::with('causer')
            ->leftJoin($userTable, function ($join) use ($activityModel, $userTable) {
                $join->on('activity_log.causer_id', '=', "{$userTable}.id")
                    ->where('activity_log.causer_type', '=', $activityModel);
            })
            ->select('activity_log.*');
    }

    /**
     * @inheritdoc
     */
    private static function userModel() : string
    {
        return config('auth.providers.users.model');
    }

    /**
     * @inheritdoc
     */
    private static function filters(Request $request) : array
    {
        $filters = [];
        foreach ($request->query() as $key => $value) {
            if ( strpos($key, 'rField-') === 0 ) {
                $filters[substr($key, 7)] = $value;
            }
        }

        return $filters;
    }

    /**
     * @inheritdoc
     */
    private static function applyFilters($query, array $filters) : void
    {
        $userTable = self::userModel()::query()->getModel()->getTable();

        foreach ($filters as $field => $value) {
            if ( $value === null || $value === '' ) {
                continue;
            }

            switch ($field) {
                case 'action':
                    $query->where('activity_log.description', 'ILIKE', '%' . $value . '%');
                    break;
                case 'user':
                    $query->where(function ($query) use ($value, $userTable) {
                        $query->where("{$userTable}.first_name", 'ILIKE', '%' . $value . '%')
                            ->orWhere("{$userTable}.last_name", 'ILIKE', '%' . $value . '%');
                    });
                    break;
                case 'userId':
                    $query->where('activity_log.causer_id', $value);
                    break;
                case 'timestamp':
                    self::applyTimestampFilter($query, $value);
                    break;
            }
        }
    }

    /**
     * @inheritdoc
     */
    private static function applyTimestampFilter($query, string $value) : void
    {
        if ( strpos($value, '-') === false ) {
            $query->whereYear('activity_log.created_at', '=', $value);

            return;
        }

        [$fromYear, $toYear] = explode('-', $value, 2);
        if ( $fromYear !== '' ) {
            $query->whereYear('activity_log.created_at', '>=', $fromYear);
        }
        if ( $toYear !== '' ) {
            $query->whereYear('activity_log.created_at', '<=', $toYear);
        }
    }
}
