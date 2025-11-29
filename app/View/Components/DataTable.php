<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DataTable extends Component
{

    public $id;
    public $columns;
    public $ajaxUrl;
    public $buttons;
    public $customOptions;

    public function __construct($id, $columns, $ajaxUrl, $buttons = [], $customOptions = [])
    {
        $this->id = $id;
        $this->columns = $columns;
        $this->ajaxUrl = $ajaxUrl;
        $this->buttons = $buttons;
        $this->customOptions = $customOptions;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        Log::info('DataTable Component Rendered', [
            'id' => $this->id,
            'columns' => $this->columns,
            'ajaxUrl'=> $this->ajaxUrl,
            'buttons'=>$this->buttons,
            'customOptions'=>$this->customOptions
        ]);
        return view('components.data-table');
    }
}
