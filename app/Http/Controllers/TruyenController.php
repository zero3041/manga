<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DanhmucTruyen;
use App\Models\Truyen;
use App\Models\Theloai;
use App\Models\ThuocDanh;
use App\Models\ThuocLoai;
use App\Models\Chapter;
use Storage;
class TruyenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:publish articles|edit articles|delete articles|add articles',['only' => ['index','show']]);
         $this->middleware('permission:add articles', ['only' => ['create','store']]);
         $this->middleware('permission:edit articles', ['only' => ['edit','update']]);
         $this->middleware('permission:delete articles', ['only' => ['destroy']]);
    }

    public function index()
    {
        $list_truyen = Truyen::with('thuocnhieudanhmuctruyen','thuocnhieutheloaitruyen')->where('loaitruyen','=',NULL)->orderBy('id','DESC')->get();
        $count =  $list_truyen->count();
        
        return view('admincp.truyen.index')->with(compact('list_truyen','count'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $theloai = Theloai::orderBy('id','DESC')->get();
        $danhmuc = DanhmucTruyen::orderBy('id','DESC')->get();
        return view('admincp.truyen.create')->with(compact('danhmuc','theloai'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'tentruyen' => 'required|unique:truyen|max:255',
                'slug_truyen' => 'required|unique:truyen|max:255',

                'hinhanh' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',

                'tomtat' => 'required',
                'truyennoibat' => 'required',
                'tukhoa' => 'required',
                'tacgia' => 'required',
                'kichhoat' => 'required',
                'hoanthien' => 'required',
                'views' => 'required',
                'danhmuc' => 'required',
                'theloai' => 'required',
            ],
            [
                'views.required' => 'Y??u c???u nh???p l?????t xem',
                'slug_truyen.unique' => 'T??n truy???n ???? c?? ,xin ??i???n t??n kh??c',
                'tentruyen.unique' => 'Slug truy???n ???? c?? ,xin ??i???n slug kh??c',
                'tentruyen.required' => 'T??n truy???n ph???i c?? nh??',
                'tukhoa.required' => 'T??? kh??a truy???n ph???i c?? nh??',
                'tomtat.required' => 'M?? t??? truy???n ph???i c?? nh??',
                'tacgia.required' => 'T??c gi??? truy???n ph???i c?? nh??',
                'slug_truyen.required' => 'Slug truy???n ph???i c??',
                'hinhanh.required' => 'H??nh ???nh truy???n ph???i c??',

            ]
        );
        // $data = $request->all();
        // // dd($data);
        $truyen = new Truyen();
        $truyen->tentruyen = $data['tentruyen'];
        $truyen->tukhoa = $data['tukhoa'];
        $truyen->slug_truyen = $data['slug_truyen'];
        $truyen->hoanthien = $data['hoanthien'];
        $truyen->tomtat = $data['tomtat'];
        $truyen->kichhoat = $data['kichhoat'];
        $truyen->tacgia = $data['tacgia'];
        $truyen->views = $data['views'];
        $truyen->truyen_noibat = $data['truyennoibat'];

        foreach($data['danhmuc'] as $key => $danh){
            $truyen->danhmuc_id = $danh[0];
        }
        $truyen->created_at = Carbon::now('Asia/Ho_Chi_Minh');
      
        foreach($data['danhmuc'] as $key => $danh){
            $truyen->danhmuc_id = $danh[0];
        }
        
        foreach($data['theloai'] as $key => $the){
            $truyen->theloai_id = $the[0];
        }
        //them anh vao folder hinh188.jpg
        $get_image = $request->hinhanh;
        $path = 'public/uploads/truyen/';
        $get_name_image = $get_image->getClientOriginalName();
        $name_image = current(explode('.',$get_name_image));
        $new_image =  $name_image.rand(0,99).'.'.$get_image->getClientOriginalExtension();
        $get_image->move($path,$new_image);
        
        $truyen->hinhanh = $new_image;
        $truyen->save();

        $truyen->thuocnhieudanhmuctruyen()->attach($data['danhmuc']);
        $truyen->thuocnhieutheloaitruyen()->attach($data['theloai']);
        
        
        
        return redirect()->back()->with('status','Th??m truy???n th??nh c??ng');

       
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $truyen = Truyen::find($id);
        
        $thuocdanhmuc = $truyen->thuocnhieudanhmuctruyen;
        $thuoctheloai = $truyen->thuocnhieutheloaitruyen;

        $theloai = Theloai::orderBy('id','DESC')->get();
        $danhmuc = DanhmucTruyen::orderBy('id','DESC')->get();     
        return view('admincp.truyen.edit')->with(compact('truyen','danhmuc','theloai','thuocdanhmuc','thuoctheloai'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate(
            [
                'views' => 'required',


                'truyennoibat' => 'required',
 
                'tentruyen' => 'required|max:255',
                'slug_truyen' => 'required|max:255',
                'tacgia' => 'required',
                'tomtat' => 'required',
                'hoanthien' => 'required',
                'kichhoat' => 'required',
                'danhmuc' => 'required',
                'theloai' => 'required',
                'tukhoa' => 'required',

            ],
            [
                'views.required' => 'Y??u c???u nh???p l?????t xem',
                'tukhoa.required' => 'T??? kh??a truy???n ph???i c?? nh??',

                'tentruyen.required' => 'T??n truy???n ph???i c?? nh??',
                'tomtat.required' => 'M?? t??? truy???n ph???i c?? nh??',
                 'tacgia.required' => 'T??c gi??? truy???n ph???i c?? nh??',
                'slug_truyen.required' => 'Slug truy???n ph???i c??',
                
            ]
        );
        // $data = $request->all();
        // dd($data);
        $truyen = Truyen::find($id);

        $truyen->thuocnhieudanhmuctruyen()->sync($data['danhmuc']);
        $truyen->thuocnhieutheloaitruyen()->sync($data['theloai']);

        $truyen->tentruyen = $data['tentruyen'];
        $truyen->tukhoa = $data['tukhoa'];
        $truyen->slug_truyen = $data['slug_truyen'];
        $truyen->hoanthien = $data['hoanthien'];
      
        $truyen->tomtat = $data['tomtat'];
        $truyen->kichhoat = $data['kichhoat'];
        $truyen->views = $data['views'];
        $truyen->tacgia = $data['tacgia'];

        $truyen->truyen_noibat = $data['truyennoibat'];
        foreach($data['danhmuc'] as $key => $danh){
            $truyen->danhmuc_id = $danh[0];
        }
         foreach($data['theloai'] as $key => $the){
            $truyen->theloai_id = $the[0];
        }
        $truyen->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        //them anh vao folder 
        $get_image = $request->hinhanh;
        if($get_image){
            $path = 'public/uploads/truyen/'.$truyen->hinhanh;
            if(file_exists($path)){
                unlink($path);
            }
            $path = 'public/uploads/truyen/';
            $get_name_image = $get_image->getClientOriginalName();
            $name_image = current(explode('.',$get_name_image));
            $new_image =  $name_image.rand(0,99).'.'.$get_image->getClientOriginalExtension();
            $get_image->move($path,$new_image);
            
            $truyen->hinhanh = $new_image;
        }
       

        $truyen->save();

        return redirect()->back()->with('status','C???p nh???t truy???n th??nh c??ng');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        $truyen = Truyen::find($id);
        $path = 'public/uploads/truyen/'.$truyen->hinhanh;
        if(file_exists($path)){
            unlink($path);
        }
        $truyen->thuocnhieudanhmuctruyen()->detach($truyen->danhmuc_id);
        $truyen->thuocnhieutheloaitruyen()->detach($truyen->theloai_id);
        // $chapter = Chapter::whereIn('truyen_id',$id)->get();
        // if($chapter->count()>0){
        //     $chapter->delete();
        // }
        Truyen::find($id)->delete();

        return redirect()->back()->with('status','X??a truy???n th??nh c??ng');
    }
    public function truyennoibat(Request $request){
        $data = $request->all();
        $truyen = Truyen::find($data['truyen_id']);
        $truyen->truyen_noibat = $data['truyennoibat'];
        $truyen->save();

    }
}
