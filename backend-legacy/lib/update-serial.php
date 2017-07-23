<?php

/* 
 * Copyright 2016 Lukas Metzger <developer@lukas-metzger.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

function update_serial($db, $domainId) {

    $db->beginTransaction();
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=:domain_id LIMIT 1");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    $content = $stmt->fetchColumn();

    $content = explode(" ", $content);    
    
    $serial = $content[2];
    
    $currentSerialDate = (int)($serial / 100);
    $currentSerialSequence = $serial % 100;
    
    $currentDate = (int)date("Ymd");
    
    if($currentDate != $currentSerialDate) {
        $newSerial = $currentDate . "00";
    } else {
        $newSerialSequence = ($currentSerialSequence+1)%100 . "";
        $newSerialSequence = str_pad($newSerialSequence, 2, "0", STR_PAD_LEFT);
        $newSerial = $currentDate . "" . $newSerialSequence;
    }
    
    $content[2] = $newSerial;
    
    
    $newsoa = implode(" ", $content);
    
    $stmt = $db->prepare("UPDATE records SET content=:content WHERE type='SOA' AND domain_id=:domain_id");
    $stmt->bindValue(':content', $newsoa, PDO::PARAM_STR);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $db->commit();

}
