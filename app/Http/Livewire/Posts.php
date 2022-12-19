<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Posts extends Component
{
    use WithFileUploads , WithPagination;
    public $user_id , $title , $body , $image , $currentImage;
    public $postId = null;
    public $showModalForm = false;
    public function showCreatePostModal()
    {
        $this->showModalForm = true;
    }

    public function updatedShowModalForm()
    {
        $this->reset();
    }

    public function storePost()
    {
        $this->validate([
            'title' => ['required'],
            'body' => ['required'],
            'image' => ['required' , 'image' , 'max:1024'],
        ]);
        $image_name = $this->image->getClientOriginalName();
        $this->image->storeAs('public/photos', $image_name);
        $post = new Post();
        $post->user_id = auth()->user()->id;
        $post->title = $this->title;
        $post->slug = Str::slug($this->title);
        $post->body = $this->body;
        $post->image = $image_name;
        $post->save();
        $this->reset();
        session()->flash('flash.banner', 'post created successfully');
        session()->flash('flash.bannerStyle', 'danger');
    }

    public function showEditPostModal($id)
    {
        $this->reset();
        $this->showModalForm = true;
        $this->postId = $id;
        $this->loadEditForm();
    }

    public function loadEditForm()
    {
        $post = Post::findOrFail($this->postId);
        $this->title = $post->title;
        $this->body = $post->body;
        $this->currentImage = $post->image;
    }

    public function updatePost()
    {
        $this->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'nullable|image|max:1024',
        ]);
        if ($this->image){
            Storage::delete('public/photos/'.$this->currentImage);
            $this->currentImage = $this->image->getClientOriginalName();
            $this->image->storeAs('public/photos/' , $this->currentImage);
        }
        Post::find($this->postId)->update([
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->currentImage,
        ]);
        $this->reset();
        session()->flash('flash.banner', 'post updated successfully');
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);
        Storage::delete('public/photos/' . $post->image);
        $post->delete();
        session()->flash('flash.banner', 'post deleted successfully');
    }

    public function render()
    {
        return view('livewire.posts' , [
            'posts' => Post::orderBy('created_at' , 'desc')->paginate(5)
        ]);
    }
}
