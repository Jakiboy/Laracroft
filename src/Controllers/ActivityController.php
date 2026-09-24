<?php

namespace Laracroft\Controllers;

use Illuminate\Http\Request;
use Laracroft\Helpers\Response;
use Laracroft\Models\Activity;
use Laracroft\Requests\FilterableRequest;
use Laracroft\Services\ActivityService;

class ActivityController extends Controller
{
    /**
     * @inheritdoc
     */
    public function index(FilterableRequest $request) : Response
    {
        return $this->success(data: ActivityService::getAll($request));
    }

    /**
     * @inheritdoc
     */
    public function show(Activity $activity) : Response
    {
        return $this->success(data: ActivityService::get($activity));
    }

    /**
     * @inheritdoc
     */
    public function search(Request $request) : Response
    {
        $search = $request->query('q');
        $items = (int)$request->query('items', 5);

        if ( !$search ) {
            return $this->error('Search term is required', code: 400);
        }

        return $this->success(data: ActivityService::search($search, $items));
    }

    /**
     * @inheritdoc
     */
    public function destroy(Activity $activity) : Response
    {
        if ( ActivityService::delete($activity) ) {
            return $this->success('Activity deleted', ['id' => $activity->id]);
        }

        return $this->error('Activity was not deleted', code: 422);
    }
}
