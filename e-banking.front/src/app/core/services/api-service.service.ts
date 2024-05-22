import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../../environments/environment.development';
import { Utilisateurs } from '../utils/interface';
import { Notify } from 'notiflix/build/notiflix-notify-aio';
import { Report } from 'notiflix/build/notiflix-report-aio';
import { Confirm } from 'notiflix/build/notiflix-confirm-aio';
import { Loading } from 'notiflix/build/notiflix-loading-aio';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {

  constructor(private http: HttpClient) { }

  login(data: any): Promise<any> {
    return new Promise<any>((resolve, reject) => {
      this.http.post<any>(`${environment.apiUrl}/utilisateur/login`, data).subscribe({
        next: (res) => {
          resolve(res);
        },
        error: (err) => {
          reject(err);
        }
      })
    })
  }

  register(data: any): Promise<any> {
    return new Promise<any>((resolve, reject) => {
      this.http.post<any>(`${environment.apiUrl}/utilisateur/register`, data).subscribe({
        next: (res) => {
          resolve(res);
        },
        error: (err) => {
          reject(err);
        }
      })
    })
  }

  getUserConnected(id: number): Promise<any> {
    return new Promise((resolve, reject) => {
      this.http.get<any>(`${environment.apiUrl}/utilisateur/get/${id}`).subscribe({
        next: (res) => {
          resolve(res)
        },
        error: (err) => {
          reject(err);
        }
      })
    })
  }

  /* aLL ABOUT notiflix */
  report(status: string, title: string, message: string) {
    if (status == 'success') {
      Report.success(
        title,
        message,
        'Okay',
      );
    } else {
      Report.failure(
        title,
        message,
        'Okay',
      );
    }
  }

  notify(status: string, message: string) {
    if (status == 'success') {
      Notify.success(message);
    } else {
      Notify.failure(message);
    }

  }

  loadingOn() {
    Loading.init({
      svgColor: '#CFE2FF',
      cssAnimation: true,
      cssAnimationDuration: 360,

    });
    Loading.hourglass();
  }

  loadingOff() {
    Loading.remove();
  }

  loadingOffAfterDelay(delay: number) {
    Loading.remove(delay);
  }

  confirm(status: string, title: string, message: string) {
    Confirm.show(
      title,
      message,
      'Oui',
      'Non',
      () => {
        // alert('Thank you.');
      },
      () => {
        // alert('If you say so...');
      },
      {
      },
    );
  }


}
