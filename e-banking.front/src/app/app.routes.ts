import { Routes } from '@angular/router';
import { LoginComponent } from './features/security/login/login.component';
import { ProfilComponent } from './features/user/profil/profil.component';
import { MainComponent } from './layouts/main/main.component';
import { HomeComponent } from './features/user/home/home.component';

export const routes: Routes = [
  { path: '', component: LoginComponent },
  {
    path: 'dashboard', component: MainComponent, children: [
      { path: '', component: HomeComponent },
      { path: 'profil', component: ProfilComponent }
    ]
  }
];
