import { Component, OnInit } from '@angular/core';
import { Utilisateurs } from '../../../core/utils/interface';
import { ApiService } from '../../../core/services/api-service.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit{
  utilisateur!: any;

  constructor(private apiService: ApiService){}


  ngOnInit(): void {
    this.userConnected();
    console.log(this.utilisateur);

   }

   userConnected(){
     const user = JSON.parse(localStorage.getItem('userConnected') || '');
    return this.apiService.getUserConnected(user.id).then(
      (res) => {
        console.log(res.data);

        this.utilisateur = res.data
      }
    ).catch(
      (err) => {
        console.log(err);

      }
    );


   }

}
