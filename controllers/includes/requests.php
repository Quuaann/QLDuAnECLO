<?php
    if (!defined('ECLO')) die("Hacking attempt");
    $requests = [
        "main"=>[
            "name"=>$jatbi->lang("Chính"),
            "item"=>[
                '/'=>[
                    "menu"=>$jatbi->lang("Trang chủ"),
                    "url"=>'/',
                    "icon"=>'<i class="ti ti-dashboard"></i>',
                    "controllers"=>"controllers/core/main.php",
                    "main"=>'true',
                    "permission" => "",
                ],
            ],
        ],
        "personnel"=>[
            "name"=>'Nhân sự',
            "item"=>[
                'project'=>[
                    "menu"=>$jatbi->lang("Dự án"),
                    "url"=>'/project',
                    "icon"=>'<i class="ti ti-clock"></i>',
                    "controllers"=>"controllers/core/project.php",
                    "main"=>'false',
                    "permission" => [
                        'project'      =>$jatbi->lang("Tăng ca"),
                        'project.add'  =>$jatbi->lang("Thêm Tăng ca"),
                        'project.edit' =>$jatbi->lang("Sửa Tăng ca"),
                        'project.deleted'=>$jatbi->lang("Xóa Tăng ca"),
                        'project.approved'=>$jatbi->lang("Cấp phép Tăng ca"),
                    ],
                ],
                'hr_config'=>[
                    "menu"=>$jatbi->lang("Chi tiết dự án"),
                    "url"=>'/staffConfiguration/department',
                    "icon"=>'<i class="ti ti-settings"></i>',
                    "controllers" => [
                        "controllers/core/staffConfiguration.php",
                        "controllers/core/latetime.php",
                        "controllers/core/timeperiod.php",
                        "controllers/core/leavetype.php",
                    ],
                    "main"=>'false',
                    "permission" => [
                        

                    ],
                ],
            ],
        ],
    ];
    foreach($requests as $request){
        foreach($request['item'] as $key_item =>  $items){
            if (is_array($items['controllers'])) {
                foreach($items['controllers'] as $controller) {
                    $setRequest[] = [
                        "key" => $key_item,
                        "controllers" => $controller,
                    ];
                }
            } else {
                $setRequest[] = [
                    "key" => $key_item,
                    "controllers" => $items['controllers'],
                ];
            }
            // Thêm controllers từ sub
            if (isset($items['sub']) && is_array($items['sub'])) {
                foreach ($items['sub'] as $sub_key => $sub_item) {
                    if (isset($sub_item['controllers'])) {
                        $setRequest[] = [
                            "key" => $sub_key,
                            "controllers" => $sub_item['controllers'],
                        ];
                    }
                }
            }
            if($items['main']!='true'){
                $SelectPermission[$items['menu']] = $items['permission'];
            }
            if (isset($items['permission']) && is_array($items['permission'])) {
                foreach($items['permission'] as $key_per => $per) {
                    $userPermissions[] = $key_per; 
                }
            }
        }
    }
?>