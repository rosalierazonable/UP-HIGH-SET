<?= $this->extend('pageTemplate');?>

<?= $this->section('content');?>
  <section id="register" class="container-fluid">
    <div class="heading text-center">
      <h1>Registration Form</h1>
    <div>

    <div class="row-md-6">
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a href="#Admin" class="nav-link active" data-toggle="tab" id="btn-admin"><input type="button" value="Admin"></a>
        </li>
        <li class="nav-item">
          <a href="#Student" class="nav-link" data-toggle="tab" id="btn-student"><input type="button" value="Student"></a>
        </li>
      </ul>
    </div>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="Admin">
        <div class="row justify-content-center ">
          <form action="<?=base_url()?>/update/add/admin" method="post" class="add">
            <div class="row">
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox fname">
                    <input type="text" name="adminFirstName" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'adminFirstName');?></span>
                    <h3>First Name</h3>
                  </div>
                </div>
              </div>
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox lname">
                    <input type="text" name="adminLastName" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'adminLastName');?></span>
                    <h3>Last Name</h3>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox uname">
                    <input type="text" name="adminUserName" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'adminUserName');?></span>
                    <h3>User Name</h3>
                  </div>
                </div>
              </div>
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox contactnum">
                    <input type="text" name="adminContactNum" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'adminContactNum');?></span>
                    <h3>Contact Number</h3>
                  </div>
                </div>
              </div>
            </div>
            <div class="row-md-4 email">
              <div class="mailBox">
                <div class="inputBox">
                  <input type="email" name="adminEmail" required>
                  <br>
                  <span class="text-danger"><?=displaySingleError($validation, 'adminEmail');?></span>
                  <h3>Email</h3>
                </div>
              </div>
            </div>
            <input type="submit" value="submit">
          </form>
        </div>
      </div>
      <div class="tab-pane fade" id="Student">
        <div class="row justify-content-center">
          <form action="<?=base_url()?>/update/add/student" method="post" class="add">
            <div class="row">
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                      <input type="text" name="studFirstName" value="<?=set_value('studFirstName');?>" required>
                      <br>
                      <span class="text-danger"><?=displaySingleError($validation, 'studFirstName');?></span>
                      <h3>First Name</h3>
                  </div>
                </div>
              </div>
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                      <input type="text" name= "studLastName" value="<?=set_value('studLastName');?>" required>
                      <br>
                      <span class="text-danger"><?=displaySingleError($validation, 'studLastName');?></span>
                      <h3>Last Name</h3>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                      <input type="text" name="studNum" value="<?=set_value('studNum');?>" required>
                      <br>
                      <span class="text-danger"><?=displaySingleError($validation, 'studNum');?></span>
                      <h3>Student Number</h3>
                  </div>
                </div>
              </div>
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                      <input type="text" name="studUserName" value="<?=set_value('studUserName');?>" required>
                      <br>
                      <span class="text-danger"><?=displaySingleError($validation, 'studUserName');?></span>
                      <h3>User Name</h3>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                    <input type="text" name="gradeLevel" value="<?=set_value('gradeLevel');?>" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'gradeLevel');?></span>
                    <h3>Grade Level</h3>
                  </div>
                </div>
              </div>
              <div class="col-md-6 half">
                <div class="form-group">
                  <div class="inputBox">
                    <input type="text" name="studContactNum" value="<?=set_value('studContactNum');?>" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'studContactNum');?></span>
                    <h3>Contact Number</h3>
                  </div>
                </div>
              </div>
            </div>
            <div class="row-md-4">
              <div class="mailBox">
                <div class="inputBox">
                    <input type="email" name="studEmail" value="<?=set_value('studEmail');?>" required>
                    <br>
                    <span class="text-danger"><?=displaySingleError($validation, 'studEmail');?></span>
                    <h3>Email</h3>
                </div>
              </div>
            </div>
            <input type="submit" value="submit">
          </form>
        </div>
      </div>
    </div>
  </section>
<?= $this->endSection();?>