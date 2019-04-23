<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Product, App\Brands, App\Category, App\Unit,
    App\TaxRate, App\VariationTemplate, App\ProductVariation,
    App\Variation, App\Business, App\BusinessLocation,
    App\Transaction;

use App\Utils\ProductUtil;

use Excel, DB;

class ProductsExportController extends Controller
{
	/**
     * All Utils instance.
     *
     */
    protected $productUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

	/**
     * Display import product screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    	if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');

        //Check if zip extension it loaded or not.
        $zip_loaded = extension_loaded('zip') ? true : false;
        $notification = [];
        if($zip_loaded === false){
        	$notification = array('success' => 0, 
                            'msg' => 'Please install/enable PHP Zip archive for import'
                        );
        }

        $locations = BusinessLocation::forDropdown($business_id);

        return view ('products_export.index')
                ->with(compact('notification', 'locations'));
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $location_id = $request->get('location_id');
            

        } catch(Exception $e){

        }
    }
}
