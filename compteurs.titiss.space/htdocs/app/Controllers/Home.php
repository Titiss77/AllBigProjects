<?php namespace App\Controllers;

use App\Models\CountdownModel;

class Home extends BaseController
{
    private function getUserToken()
    {
        $token = $this->request->getCookie('timer_user_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(16)); 
            setcookie('timer_user_token', $token, time() + 315360000, '/');
        }
        return $token;
    }

    public function index()
    {
        $model = new CountdownModel();
        $token = $this->getUserToken();
        
        $record = $model->where('user_token', $token)->first();
        
        if ($record) {
            $model->update($record['id'], ['last_active' => date('Y-m-d H:i:s')]);
            $data['countdown'] = $record;
        } else {
            $data['countdown'] = null;
        }

        if (rand(1, 20) === 1) {
            $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
            $model->where('last_active <', $sixMonthsAgo)->delete();
        }
        
        return view('home', $data);
    }

    public function edit()
    {
        $model = new CountdownModel();
        $token = $this->getUserToken();
        
        $data['countdown'] = $model->where('user_token', $token)->first();
        $data['type'] = $this->request->getGet('type');
        
        return view('edit', $data);
    }

    public function update()
    {
        $model = new CountdownModel();
        $token = $this->getUserToken();
        
        $newTargetDate = $this->request->getPost('countdown_date');
        $newPastDate = $this->request->getPost('past_date');

        $updateData = [
            'last_active' => date('Y-m-d H:i:s')
        ];
        
        // Si le champ est envoyé mais qu'il est vide, on le force à NULL pour éviter les 0000-00-00
        if ($newTargetDate !== null) {
            $updateData['countdown_date'] = ($newTargetDate === '') ? null : $newTargetDate;
        }
        if ($newPastDate !== null) {
            $updateData['past_date'] = ($newPastDate === '') ? null : $newPastDate;
        }

        $existingRecord = $model->where('user_token', $token)->first();
        
        if ($existingRecord) {
            $model->update($existingRecord['id'], $updateData);
        } else {
            // Première insertion : on force NULL si la date n'est pas fournie
            if (!isset($updateData['countdown_date'])) $updateData['countdown_date'] = null;
            if (!isset($updateData['past_date'])) $updateData['past_date'] = null;
            
            $updateData['user_token'] = $token;
            $model->insert($updateData);
        }

        return redirect()->to('/');
    }
}