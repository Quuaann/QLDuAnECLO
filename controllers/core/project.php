<?php
    if (!defined('ECLO')) die("Hacking attempt");
    $jatbi = new Jatbi($app);
    $setting = $app->getValueData('setting');

    // Danh sách dự án
    $app->router("/project", 'GET', function($vars) use ($app, $jatbi, $setting) {
        $vars['title'] = $jatbi->lang("Danh sách Dự án");  
        echo $app->render('templates/project.html', $vars);
    })->setPermissions(['project']);

    $app->router("/project", 'POST', function($vars) use ($app, $jatbi) {
        $app->header([
            'Content-Type' => 'application/json',
        ]);

        // Nhận dữ liệu từ DataTable
        $draw = $_POST['draw'] ?? 0;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $trangthai = $_POST['trangthai'] ?? '';
        $ngaybatdau = $_POST['ngaybatdau'] ?? '';
        $ngayketthuc = $_POST['ngayketthuc'] ?? '';

        // Fix lỗi ORDER cột
        $orderColumnIndex = $_POST['order'][0]['column'] ?? 1; // Mặc định cột "ten"
        $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');

        // Danh sách cột hợp lệ
        $validColumns = ["id", "ten", "khachhang", "ngaybatdau", "ngayketthuc", "trangthai"];
        $orderColumn = $validColumns[$orderColumnIndex] ?? "ten";

        // Điều kiện lọc dữ liệu
        $conditions = ["AND" => []];

        // Tìm kiếm toàn cục (searchValue)
        if (!empty($searchValue)) {
            $conditions["AND"]["OR"] = [
                "ten[~]" => $searchValue,
                "khachhang[~]" => $searchValue,
                "ngaybatdau[~]" => $searchValue,
                "ngayketthuc[~]" => $searchValue,
            ];
        }

        // Lọc theo trạng thái (trangthai)
        if ($trangthai !== '') {
            $conditions["AND"]["trangthai"] = (bool)$trangthai;
        }

        // Lọc theo ngày bắt đầu
        if (!empty($ngaybatdau)) {
            $conditions["AND"]["ngaybatdau[>=]"] = $ngaybatdau;
        }

        // Lọc theo ngày kết thúc
        if (!empty($ngayketthuc)) {
            $conditions["AND"]["ngayketthuc[<=]"] = $ngayketthuc;
        }

        // Kiểm tra nếu conditions bị trống, tránh lỗi SQL
        if (empty($conditions["AND"])) {
            unset($conditions["AND"]);
        }

        // Đếm tổng số bản ghi (không dùng LIMIT)
        $count = $app->count("project", "*", $conditions);

        // Truy vấn danh sách dự án
        $datas = $app->select("project", "*", array_merge($conditions, [
            "LIMIT" => [$start, $length],
            "ORDER" => [$orderColumn => $orderDir]
        ])) ?? [];

        // Xử lý dữ liệu đầu ra
        $formattedData = array_map(function($data) use ($app, $jatbi) {
            return [
                "checkbox" => $app->component("box", ["data" => $data['id']]),
                "id" => $data['id'],
                "ten" => $data['ten'],
                "khachhang" => $data['khachhang'] ?? $jatbi->lang("Không xác định"),
                "ngaybatdau" => $data['ngaybatdau'],
                "ngayketthuc" => $data['ngayketthuc'],
                "trangthai" => $app->component("status",["url"=>"/project-status/".$data['id'],"data"=>$data['trangthai']]),
                "action" => $app->component("action", [
                    "button" => [          
                        [
                            'type' => 'button',
                            'name' => $jatbi->lang("Sửa"),
                            'permission' => ['insurance.edit'],
                            'action' => ['data-url' => '/project-edit?id=' . $data['id'], 'data-action' => 'modal']
                        ],
                        [
                            'type' => 'button',
                            'name' => $jatbi->lang("Xóa"),
                            'permission' => ['insurance.deleted'],
                            'action' => ['data-url' => '/procject-deleted?id=' . $data['id'], 'data-action' => 'modal']
                        ],
                    ]
                ]),            ];
        }, $datas);

        // Log dữ liệu đã format trước khi JSON encode
        error_log("Formatted Data: " . print_r($formattedData, true));
    
        // Kiểm tra lỗi JSON
        $response = json_encode([
            "draw" => $draw,
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "data" => $formattedData
        ]);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Encode Error: " . json_last_error_msg());
        }
    
        echo $response;
    })->setPermissions(['project']);

    // Thêm dự án
    $app->router("/project-add", 'GET', function($vars) use ($app, $jatbi, $setting) {
        $vars['title'] = $jatbi->lang("Thêm Dự án");
        echo $app->render('templates/project/add.html', $vars, 'global');
    })->setPermissions(['project.add']);

    $app->router("/project-add", 'POST', function($vars) use ($app, $jatbi) {
        $app->header(['Content-Type' => 'application/json']);

        $ten = isset($_POST['ten']) ? $app->xss($_POST['ten']) : '';
        $khachhang = isset($_POST['khachhang']) ? $app->xss($_POST['khachhang']) : '';
        $ngaybatdau = isset($_POST['ngaybatdau']) ? $app->xss($_POST['ngaybatdau']) : '';
        $ngayketthuc = isset($_POST['ngayketthuc']) ? $app->xss($_POST['ngayketthuc']) : '';
        $trangthai = isset($_POST['trangthai']) ? (bool)$app->xss($_POST['trangthai']) : false;

        if (empty($ten) || empty($ngaybatdau) || empty($ngayketthuc)) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Vui lòng không để trống")]);
            return;
        }

        if (strtotime($ngaybatdau) > strtotime($ngayketthuc)) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Ngày bắt đầu không được sau ngày kết thúc")]);
            return;
        }

        $insert = [
            "ten" => $ten,
            "khachhang" => $khachhang,
            "ngaybatdau" => $ngaybatdau,
            "ngayketthuc" => $ngayketthuc,
            "trangthai" => $trangthai
        ];

        $jatbi->logs('project', 'project-add', $insert);
        $app->insert("project", $insert);

        echo json_encode(["status" => "success", "content" => $jatbi->lang("Thêm dự án thành công")]);
    })->setPermissions(['project.add']);

    // Sửa dự án
    $app->router("/project-edit", 'GET', function($vars) use ($app, $jatbi) {
        $vars['title'] = $jatbi->lang("Sửa Dự án");
        $id = isset($_GET['id']) ? $app->xss($_GET['id']) : null;

        if (!$id) {
            echo $app->render('templates/common/error-modal.html', $vars, 'global');
            return;
        }

        $vars['data'] = $app->get("project", "*", ["id" => $id]);
        if ($vars['data']) {
            echo $app->render('templates/project/edit.html', $vars, 'global');
        } else {
            echo $app->render('templates/common/error-modal.html', $vars, 'global');
        }
    })->setPermissions(['project.edit']);

    $app->router("/project-edit", 'POST', function($vars) use ($app, $jatbi) {
        $app->header(['Content-Type' => 'application/json']);

        $id = isset($_POST['id']) ? $app->xss($_POST['id']) : null;
        if (!$id) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Mã dự án không hợp lệ")]);
            return;
        }

        $ten = isset($_POST['ten']) ? $app->xss($_POST['ten']) : '';
        $khachhang = isset($_POST['khachhang']) ? $app->xss($_POST['khachhang']) : '';
        $ngaybatdau = isset($_POST['ngaybatdau']) ? $app->xss($_POST['ngaybatdau']) : '';
        $ngayketthuc = isset($_POST['ngayketthuc']) ? $app->xss($_POST['ngayketthuc']) : '';
        $trangthai = isset($_POST['trangthai']) ? (bool)$app->xss($_POST['trangthai']) : false;

        if (empty($ten) || empty($ngaybatdau) || empty($ngayketthuc)) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Vui lòng không để trống")]);
            return;
        }

        if (strtotime($ngaybatdau) > strtotime($ngayketthuc)) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Ngày bắt đầu không được sau ngày kết thúc")]);
            return;
        }

        $update = [
            "ten" => $ten,
            "khachhang" => $khachhang,
            "ngaybatdau" => $ngaybatdau,
            "ngayketthuc" => $ngayketthuc,
            "trangthai" => $trangthai
        ];

        $app->update("project", $update, ["id" => $id]);
        $jatbi->logs('project', 'project-edit', $update);

        echo json_encode(["status" => "success", "content" => $jatbi->lang("Cập nhật dự án thành công")]);
    })->setPermissions(['project.edit']);

    // Xóa dự án
    $app->router("/project-delete", 'POST', function($vars) use ($app, $jatbi) {
        $app->header(['Content-Type' => 'application/json']);

        $id = isset($_POST['id']) ? $app->xss($_POST['id']) : null;
        if (!$id) {
            echo json_encode(["status" => "error", "content" => $jatbi->lang("Mã dự án không hợp lệ")]);
            return;
        }

        $app->delete("project", ["id" => $id]);
        $jatbi->logs('project', 'project-delete', ["id" => $id]);

        echo json_encode(["status" => "success", "content" => $jatbi->lang("Xóa dự án thành công")]);
    })->setPermissions(['project.delete']);

    //Cấp phép insurance
    $app->router("/project-status/{id}", 'POST', function($vars) use ($app, $jatbi) {
        $app->header([
            'Content-Type' => 'application/json',
        ]);

        $data = $app->get("project","*",["id"=>$vars['id']]);
        if($data>1){
            if($data>1){
                if($data['trangthai']==='A'){
                    $status = "D";
                } 
                elseif($data['trangthai']==='D'){
                    $status = "A";
                }
                $app->update("project",["trangthai"=>$status],["id"=>$data['id']]);
                $jatbi->logs('project','project-status',$data);
                echo json_encode(value: ['status'=>'success','content'=>$jatbi->lang("Cập nhật thành công")]);
            }
            else {
                echo json_encode(['status'=>'error','content'=>$jatbi->lang("Cập nhật thất bại"),]);
            }
        }
        else {
            echo json_encode(["status"=>"error","content"=>$jatbi->lang("Không tìm thấy dữ liệu")]);
        }
    });