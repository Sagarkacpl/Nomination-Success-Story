<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="split-container">
       <div class="split-left d-none d-lg-flex">
              <div class="left-content">
                     <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="assets/images/icai-75-logo.jpg" alt="ICAI" height="60">
                     </div>
                     <h5 class="text-white-50 fw-normal">The Institute of Chartered Accountants of India</h5>
                     <p class="text-white-50 small">(Set up by an Act of Parliament)</p>
                     <h6 class="text-white-50">Women &amp; Young Members Excellence Committee (WYMEC)</h6>
                     <p class="text-white-50">Invites your nomination for</p>
                     <h1 class="display-5 fw-bold text-gold">3<sup>rd</sup> CA Women Excellence <span
                                   class="text-white">Awards 2025</span></h1>
                     <p class="text-white-50">Recognizing and Acknowledging Exemplary Contribution of CA Women Members
                     </p>
              </div>
       </div>

       <div class="split-right">
              <div class="form-card">
                     <div class="text-center mb-4">
                            <img src="assets/images/icai-75-logo.jpg" alt="ICAI" height="55" class="mb-2">
                            <h5 class="fw-bold mb-0">The Institute of Chartered Accountants of India</h5>
                            <p class="text-muted small mb-1">(Set up by an Act of Parliament)</p>
                            <p class="fw-semibold small mb-0">Women &amp; Young Members Excellence Committee (WYMEC) of
                                   ICAI</p>
                            <h4 class="fw-bold mt-2" style="color:#8e1e8e;">3rd CA Women Excellence Awards</h4>
                            <p class="text-muted small">Recognizing and Acknowledging exemplary contribution of CA Women
                                   Members</p>
                     </div>

                     <h6 class="text-muted mb-3">Create New Account</h6>

                     <?php require __DIR__ . '/../partials/flash.php'; ?>

                     <form action="register" method="POST" enctype="multipart/form-data" id="registerForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                            <div class="mb-3">
                                   <label for="full_name" class="form-label">Full Name</label>
                                   <input type="text" class="form-control form-control-lg" id="full_name"
                                          name="full_name" required maxlength="150"
                                          value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                   <div class="invalid-feedback">Please enter your full name.</div>
                            </div>

                            <div class="mb-3">
                                   <label for="email" class="form-label">Email Address</label>
                                   <input type="email" class="form-control form-control-lg" id="email" name="email"
                                          required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                   <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-3">
                                   <label for="password" class="form-label">Password</label>
                                   <div class="input-group input-group-lg">
                                          <input type="password" class="form-control" id="password" name="password"
                                                 required minlength="8">
                                          <button class="btn btn-outline-secondary toggle-password" type="button"
                                                 data-target="password">
                                                 <i class="bi bi-eye"></i>
                                          </button>
                                          <div class="invalid-feedback">At least 8 characters, letters and numbers.
                                          </div>
                                   </div>
                            </div>

                            <div class="mb-3">
                                   <label for="confirm_password" class="form-label">Confirm Password</label>
                                   <div class="input-group input-group-lg">
                                          <input type="password" class="form-control" id="confirm_password"
                                                 name="confirm_password" required minlength="8">
                                          <button class="btn btn-outline-secondary toggle-password" type="button"
                                                 data-target="confirm_password">
                                                 <i class="bi bi-eye"></i>
                                          </button>
                                          <div class="invalid-feedback">Passwords do not match.</div>
                                   </div>
                            </div>

                            <div class="mb-3">
                                   <label for="mobile" class="form-label">Mobile No</label>
                                   <input type="text" class="form-control form-control-lg" id="mobile" name="mobile"
                                          required pattern="[6-9][0-9]{9}"
                                          value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>">
                                   <div class="invalid-feedback">Enter a valid 10-digit mobile number.</div>
                            </div>

                            <div class="mb-4">
                                   <label for="profile_pic" class="form-label">Profile Picture (optional)</label>
                                   <input type="file" class="form-control form-control-lg" id="profile_pic"
                                          name="profile_pic" accept=".jpg,.jpeg,.png,.webp">
                            </div>

                            <div class="d-flex gap-2">
                                   <button type="submit" class="btn text-white flex-fill"
                                          style="background:#8e1e8e;">Sign Up</button>
                                   <a href="./" class="btn btn-outline-primary flex-fill">Login</a>
                            </div>

                            <p class="text-muted small mt-3 mb-0">If you have already created an account, click Login.
                            </p>
                     </form>
              </div>
       </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>