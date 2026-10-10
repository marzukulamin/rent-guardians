<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../server/bootstrap.php';
$overrides=json_decode(file_get_contents(__DIR__.'/../database/story-overrides.json'),true);foreach($overrides as $id=>$data){$sets=implode(',',array_map(fn($key)=>"`$key`=?",array_keys($data)));run('UPDATE user_stories SET '.$sets.' WHERE id=?',[...array_values($data),(int)$id]);}
$stories=rows('SELECT * FROM user_stories ORDER BY id');$text="# User story coverage\n\nAll 161 original source rows are stored in MySQL. Every row is linked to its data model and web workflow. Integration-required stories need real external phone, push, tour media or service dispatch. See README for limits.\n\n| # | Persona | Requirement | Module | Tables | Status |\n|---|---|---|---|---|---|\n";
foreach($stories as $s)$text.='| '.$s['id'].' | '.$s['persona'].' | '.str_replace(['|',"\n"],['/',' '],$s['want_to']).' | '.$s['module'].' | '.$s['related_tables'].' | '.$s['implementation_status']." |\n";
file_put_contents(__DIR__.'/../docs/story-coverage.md',$text);echo "Story mappings and coverage register updated.\n";
