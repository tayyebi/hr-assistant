<div class="page-header">
    <h1>New Announcement</h1>
    <a href="<?= $prefix ?>/announcements" class="btn">Back</a>
</div>

<section>
    <form method="post">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="content" rows="10" required></textarea>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_pinned" value="1">
                Pin this announcement
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Post Announcement</button>
    </form>
</section>
