import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ApiService } from '../../../core/services/api-service.service';
import { Utilisateurs } from '../../../core/utils/interface';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  passwordInputState: boolean = false;

  changeForm: boolean = false;

  loginForm: FormGroup;
  RegisterForm: FormGroup;

  submitLoginForm: boolean = false;
  submitRegisterForm: boolean = false;

  constructor(private fb: FormBuilder, private apiService: ApiService, private route: Router) {
    this.loginForm = fb.group({
      telephone: ["", [Validators.required, Validators.pattern(/^(77|78|76)\d{7}$/)]],
      password: ["", [Validators.required, Validators.minLength(6)]]
    });

    this.RegisterForm = fb.group({
      nomComplet: ["", [Validators.required, Validators.minLength(6)]],
      telephone: ["", [Validators.required, Validators.pattern(/^(77|78|76)\d{7}$/)]],
      password: ["", [Validators.required, Validators.minLength(6)]],
      code_pin: ["", [Validators.required, Validators.maxLength(4), Validators.minLength(4)]],
      email: ["", [Validators.email]]
    })
  }


  togglePassword() {
    this.passwordInputState = !this.passwordInputState;
  }

  onChangeForm() {
    this.changeForm = !this.changeForm;
  }

  get loginFomElement(): any {
    return this.loginForm.controls
  }

  get registerFomElement(): any {
    return this.RegisterForm.controls
  }

  resetLoginForm() {
    this.submitLoginForm = false;
    this.loginForm.reset();
  }

  resetRegisterForm() {
    this.submitRegisterForm = false;
    this.RegisterForm.reset();
  }

  onSubmitLoginForm() {
    this.submitLoginForm = true;

    if (this.loginForm.invalid) {
      return;
    }

    this.onLogin(this.loginForm.value);
  }

  onSubmitRegisterForm() {
    this.submitRegisterForm = true;

    if (this.registerFomElement.invalid) {
      return;
    }

    const newUser = this.RegisterForm.value;
    newUser.id_role = 3

    if (newUser.email != "" && this.registerFomElement.email.errors) {
      return;
    }

    this.onRegister(newUser);
  }

  onLogin(data: any) {
    this.apiService.loadingOn();
    this.submitLoginForm
    return this.apiService.login(data).then(
      (res) => {
        if (res.data) {
          this.resetLoginForm();
          localStorage.setItem('userConnected', JSON.stringify(res.data));
          this.route.navigate(['/dashboard']);
          this.apiService.loadingOff();
          this.apiService.notify('success', `Bienvenue ${res.data.nomComplet}`);
        }
      }
    ).catch(
      (err) => {
        if (err.status == 503) {
          this.apiService.loadingOff();
          this.apiService.report('failure', "Email ou mot de passe incorect", "");
        }
      }
    )
  }

  onRegister(data: any) {
    this.apiService.loadingOn();
    this.submitRegisterForm
    return this.apiService.register(data).then(
      (res) => {
        if (res.status_code == 200) {
          this.apiService.loadingOff();
          this.resetRegisterForm();
          this.onChangeForm();
        }
      }
    ).catch(
      (err) => {
        if(err.status == 200){
          this.apiService.report('failure', "Ce numéro à dèja un compte", "");
          this.apiService.loadingOff();
        }
        console.log(err);
      }
    )
  }
}
