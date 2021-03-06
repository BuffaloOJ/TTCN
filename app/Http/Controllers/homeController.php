<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller;
use App\Repositories\UserRepository;
use Session;
use Auth;
use App\bomon;
use App\diem;
use App\giangvien;
use App\loaitin;
use App\lop;
use App\monhoc;
use App\sinhvien;
use App\tintuc;
use App\User;
use Illuminate\Support\Str;

class homeController extends sharecontroller
{
    function __construct() { 
        view::share('stt','1');
        $sinhvien = sinhvien::get();
        view::share('sinhvien',$sinhvien);
        $monhoc = monhoc::get();
        view::share('monhoc',$monhoc);
        $giangvien = giangvien::get();
        view::share('giangvien',$giangvien);
        $user = user::get();
        view::share('user',$user);
        $menu = loaitin::where('menu','1')->get();
        view::share('menu',$menu);
        $gioithieu = loaitin::where('gioithieu','1')->firstOrFail();
        $menuGioiThieu = tintuc::where('idlt',$gioithieu->id)->get();
        view::share('gioithieu',$menuGioiThieu);
        $gtlist = loaitin::join('tintucs','loaitins.id','tintucs.idlt')->where('gioithieu','1')->get();
        view::share('gtlist',$gtlist);
        $loaitin = loaitin::all();
        view::share('loaitin',$loaitin);
        $thongbaochinh = tintuc::where('thongbaochinh','1')->orderBy('created_at','desc')->take(6)->get();
        view::share('thongbaochinh',$thongbaochinh);
        $tintuc = tintuc::join('loaitins','tintucs.idlt','loaitins.id')
        ->orderBy('created_at','desc')
        ->select('tintucs.id','tintucs.tieude','tintucs.img','loaitins.tenkhongdau','tintucs.video','tintucs.slide','loaitins.tenloaitin','tintucs.created_at','tintucs.thongbaochinh')->get();
        view::share('tintuc',$tintuc);
        $this->middleware(function ($request, $next) {
        $this->id = Auth::user();
        if($this->id!=null){
            $id = Auth::user()->id;
            $sinhvien = sinhvien::where('idusers',$id)->get();
            $giangvien = giangvien::where('idusers',$id)->get();    
            view::share('sinhvien',$sinhvien);
            view::share('giangvien',$giangvien);
            view::share('iduser',$id);
            return $next($request);
        }else{
            return $next($request);
        }
    });
    }
    public function getHome(){
        $slides = tintuc::where('slide','1')->orderBy('created_at','desc')->take(5)->get();
        $loaitin = loaitin::where('menu','1')->get();
        $box = array();
        foreach($loaitin as $lt){
            $box[$lt->tenloaitin] = loaitin::find($lt->id)->tintuc->take(5);
        }
        return view('noidung.trangchu',['slides'=>$slides,'box'=>$box]);
    }

    //tintuc
    public function getListtintuc(){
        return view('admin.tintuc.list');
    }

    public function showAdd(){
        $loaitin = loaitin::all();
        return view('admin.tintuc.add',['loaitin'=>$loaitin]);
    }

    public function addTin(Request $request){
        $this->validate($request,[
            'tieude'=>'required|min:5',
            'upload'=>'required',
            'tomtat'=>'required|min:5',
            'loaitin'=>'required',
            'noidung'=>'required'
        ],[
            'tieude.required'=>'Chưa Nhập Tiêu Đề',
            'tieude.min'=>'Tiêu Đề Cần Tối Thiểu 5 Ký Tự',
            'upload.required'=>'Chưa Chọn Hình Ảnh',
            'tomtat.required'=>'Chưa Nhập Tóm Tắt',
            'loaitin.required'=>'Chưa Chọn Loại Tin',
            'noidung.required'=>'Chưa Nhập Nội Dung'
        ]);
        $tintuc = new tintuc;
        $tintuc->tieude = $request->tieude;
        $tintuc->tenkhongdau = strtolower(convert_vi_to_en($request->tieude));
        $tintuc->tomtat = $request->tomtat;
        $tintuc->idlt = $request->loaitin;
        $tintuc->slide = 0;
        $tintuc->thongbaochinh = 0;
        $name = Str::random(10);
        $file = $request->file('upload');
        $file->move('img',$name);
        $tintuc->img = 'img/'.$name;
        $tintuc->video = $request->video;
        $tintuc->noidung = $request->noidung;
        $tintuc->save();
        return redirect()->route('tintuc')->with('thongbao','Đã thêm thành công.');
    }

    public function showEdit($id){
        $tintuc = tintuc::find($id);
        $loaitin = loaitin::all();
        return view('admin.tintuc.edit',['tintuc'=>$tintuc,'loaitin'=>$loaitin]);
    }

    public function editTin(Request $request){
        $this->validate($request,[
            'tieude'=>'required|min:5',
            'tomtat'=>'required|min:5',
            'loaitin'=>'required',
            'noidung'=>'required'
        ],[
            'tieude.required'=>'Chưa Nhập Tiêu Đề',
            'tieude.min'=>'Tiêu Đề Cần Tối Thiểu 5 Ký Tự',
            'tomtat.required'=>'Chưa Nhập Tóm Tắt',
            'loaitin.required'=>'Chưa Chọn Loại Tin',
            'noidung.required'=>'Chưa Nhập Nội Dung'
        ]);
        $tintuc = tintuc::find($request->id);
        $tintuc->tieude = $request->tieude;
        $tintuc->tenkhongdau = strtolower(convert_vi_to_en($request->tieude));
        $tintuc->tomtat = $request->tomtat;
        $tintuc->idlt = $request->loaitin;
        if(isset($request->upload)){
            $name = Str::random(10);
            $file = $request->file('upload');
            $file->move('img',$name.'.png');
            $tintuc->img = 'img/'.$name.'.png';
        }
        $tintuc->noidung = $request->noidung;
        $tintuc->save();
        return redirect()->route('tintuc')->with('thongbao','Đã thêm thành công.');
    }
    public function deltin($id){
        $tintuc = tintuc::where('id',$id)->delete();
        return redirect()->route('tintuc');
    }
    public function viewTin($tieude){
        $id = getid($tieude);
        $tintuc = tintuc::findOrFail($id);
        return view('noidung.view',['tintuc'=>$tintuc]);
    }

    public function listNews($tieude){
        $id = getid($tieude);
        $loaitin = loaitin::findOrFail($id);
        $tintuc = tintuc::where('idlt',$id)->orderBy('created_at','desc')->paginate(10);
        return view('noidung.listnews',['loaitin'=>$loaitin,'tintuc'=>$tintuc]);
    }

    public function changeSlide(Request $request){
        $tintuc = tintuc::find($request->id);
        $tintuc->slide = $request->slide;
        $tintuc->save();
    }

    public function changeThongBao(Request $request){
        $tintuc = tintuc::find($request->id);
        $tintuc->thongbaochinh = $request->thongbaochinh;
        $tintuc->save();
    }
    //loaitin
    public function getListloaitin(){
        return view('admin.loaitin.list');
    }

    public function addLoaiTin(Request $request){
        $this->validate($request,[
            'tenloaitin' => 'required|min:5|max:255'
        ],[
            'tenloaitin.required' => 'Bạn chưa nhập tên loại tin',
            'tenloaitin.min' => 'Tên loại tin có tối thiểu 5 ký tự',
            'tenloaitin.max' => 'Tên loại tin có tối đa 255 ký tự'
        ]);
        $loaitin = new loaitin;
        $loaitin->tenloaitin = $request->tenloaitin;
        $loaitin->tenkhongdau = convert_vi_to_en($request->tenloaitin);
        $loaitin->menu = 0;
        $loaitin->gioithieu = 0;
        $loaitin->save();
        return redirect()->route('loaitin')->with('thongbao','Đã thêm thành công.');
    }

    public function changeMenu(Request $request){
        $loaitin = loaitin::find($request->id);
        $loaitin->menu = $request->menu;
        $loaitin->save();
    }

    public function changeGioiThieu(Request $request){
        $loaitin = loaitin::find($request->id);
        $loaitin->gioithieu = $request->gioithieu;
        $loaitin->save();	
    }

    public function editLoaiTin(Request $request){
        $loaitin = loaitin::find($request->id);
        $loaitin->tenloaitin = $request->tenloaitin;
        $loaitin->tenkhongdau = convert_vi_to_en($request->tenloaitin);
        $loaitin->save();
        return redirect()->route('loaitin')->with('thongbao','Đã cập nhật thành công.');
    }
    public function deltl($id){
        $tintuc = loaitin::where('id',$id)->delete();
        return redirect()->route('loaitin');
    }
    //user
    public function Admin(){
        return view('admin.addadmin');
    }

    public function addUser(Request $request){
        $this->validate($request,[
            'email'=>'required|email|unique:users,email',
            'password'=> 'required|min:6|max:32',
            'level'=>'required'
        ],[
            'email.required'=>'Chưa nhập email',
            'email.email'=>'Email không đúng định dạng',
            'email.unique'=>'Email đã có người đăng ký',
            'password.required'=>'Chưa nhập mật khẩu',
            'password.min:6'=>'Mật khẩu phải chứa nhiều hơn 6 ký tự và ít hơn 32 ký tự',
            'password.max:32'=>'Mật khẩu phải chứa nhiều hơn 6 ký tự và ít hơn 32 ký tự',
            'level.required'=>'Chưa phân quyền'
        ]);
        $user = new User;
        $user->email= $request->email;
        $user->password = bcrypt($request->password);
        $user->level= $request->level;
        $user->save();
        return redirect()->route('admin')->with('thongbao','Đã thêm thành công.');
    }
    public function editUser(Request $request){
        $user = User::find($request->id);
        $user->email = $request->email;
        $user->level = $request->level;
        $user->save();
        return redirect()->route('admin')->with('thongbao','Đã cập nhật thành công.');
    }
    public function delUser($id){
        $user = user::where('id',$id)->delete();
        return redirect()->route('admin');
    }
    public function bangdiem(){
        $sinhvien = sinhvien::join('lop','lop.id','sinhvien.idlop')
        ->join('diems','diems.idsv','sinhvien.id')->get();
        // $diem = diem::join('sinhvien','sinhvien.id','diems.idsv')
        // ->join('monhocs','monhocs.id','diems.idmonhoc')
        // ->join('giangvien','giangvien.id','diems.idgv')
        // ->select('sinhvien.*','monhocs.*','giangvien.*')
        // ->get();
        $diem = array(diem::get(),monhoc::get(),giangvien::get());
        return view('noidung.bangdiem',['sinhvien'=>$sinhvien,'diem'=>$diem]);
    }
    public function editbangdiem(Request $request){
        $diem = diem::find($request->id);
        $diem->cc = $request->cc;
        $diem->tx = $request->tx;
        $diem->gk = $request->gk;
        $diem->kt = $request->kt;
        $diem->idgv = $request->idgv;
        $diem->save();
        return redirect()->route('bangdiem');
    }
    public function addbangdiem(Request $request){
        $sinhvien = sinhvien::get();
        $diem = new diem;
        $diem->idmonhoc = $request->idmh;
        $diem->idgv = $request->idgv;
        foreach($sinhvien as $sv){
            $diem->idsv = $sv->id;
            $diem->save();
        }
        return redirect()->route('bangdiem');
    }
}
