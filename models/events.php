<?php
class Events{
    private $id_event;
    private $user_id;
    private $title;
    private $description;
    private $event_type;
    private $start_date;
    private $end_date;
    private $status;
    private $created_at;
    private $updated_at;
    public function __construct(
    $id_event = null,
    $user_id = null,
    $title = null,
    $description = null,
    $event_type = 'general',
    $start_date = null,
    $end_date = null,
    $status = 'upcoming',
    $created_at = null,
    $updated_at = null
) {
    $this->id_event = $id_event;
    $this->user_id = $user_id;
    $this->title = $title;
    $this->description = $description;
    $this->event_type = $event_type;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->status = $status;
    $this->created_at = $created_at;
    $this->updated_at = $updated_at;
}

    public function getIdEvent(){
        return $this->id_event;
    }
    public function getUserId(){
        return $this->user_id;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getDescription(){
        return $this->description;
    }
    public function getEventType(){
        return $this->event_type;
    }
    public function getStartDate(){
        return $this->start_date;
    }
    public function getEndDate(){
        return $this->end_date;
    }
    public function getStatus(){
        return $this->status;
    }
    public function getCreatedAt(){
        return $this->created_at;
    }
    public function getUpdatedAt(){
        return $this->updated_at;
    }
    public function setTitle($title){
        $this->title = $title;
    }
    public function setDescription($description){
        $this->description = $description;
    }
    public function setEventType($event_type){
        $this->event_type = $event_type;
    }
    public function setStartDate($start_date){
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date){
        $this->end_date = $end_date;
    }
    public function setStatus($status){
        $this->status = $status;
    }
    public function setUpdatedAt($updated_at){
        $this->updated_at = $updated_at;
    }
        public function setCreatedAt($created_at){
        $this->created_at = $created_at;
    }
        public function setUserId($user_id){
        $this->user_id = $user_id;
    }

}





//id_event	user_id	title	description	event_type	start_date	end_date	status	created_at	updated_at	
