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
                'attendance'=>[
                    "menu"=>$jatbi->lang("Chấm công"),
                    "url"=>'/manager/attendance',
                    "icon"=>'<i class="ti ti-check"></i>',
                    "controllers"=>"controllers/core/attendance.php",
                    "main"=>'false',
                    "permission"=>[
                        'attendance'    =>$jatbi->lang("Chấm công"),
                       
                    ]
                ],
            ],
        ],
    ];
    foreach($requests as $request){
        foreach($request['item'] as $key_item =>  $items){
            $setRequest[] = [
                "key" => $key_item,
                "controllers" =>  $items['controllers'],
            ];
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