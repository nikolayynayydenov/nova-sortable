<?php

namespace Ofcold\NovaSortable\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

use Illuminate\Support\Facades\Log;


class SortableController extends Controller
{
	public function store(NovaRequest $request)
	{
		$items = json_decode(base64_decode($request->items));

		if ($request->isViaManyToMany) {
			$relationship = $request->newResource()->model()
				->{$request->viaResource}();

			$model = $relationship->getPivotClass();

			$foreignPivotKey = $relationship->getForeignPivotKeyName();

			$relatedPivotKey = $relationship->getRelatedPivotKeyName();

			foreach ($items as $item) {
				tap($model::where($foreignPivotKey, $item->id)
						->where($relatedPivotKey, $request->viaResourceId)
						->first(), 
					function($entry) use ($model, $item) {
						$entry->{$model::orderColumnName()} = $item->sort_order;
					}
				)->save();
			}
		} else {
			$model = get_class($request->newResource()->model());

			foreach ($items as $item) {
				tap($model::find($item->id), function($entry) use ($model, $item) {
					$entry->{$model::orderColumnName()} = $item->sort_order;
				})->save();
			}
		}

		return response()->json();
	}
}
