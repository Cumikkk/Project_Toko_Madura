/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     20/07/2026 13.02.43                          */
/*==============================================================*/


drop table if exists barang;

drop table if exists investor;

drop table if exists laporan_omzet;

drop table if exists outlet;

drop table if exists pengaturan_sistem;

drop table if exists rekap_bagi_hasil;

drop table if exists users;

/*==============================================================*/
/* Table: barang                                                */
/*==============================================================*/
create table barang
(
   id_barang            int not null,
   id_outlet            int,
   nama_barang          varchar(100),
   stok                 int,
   harga_jual           decimal(15,2),
   created_at           timestamp,
   updated_at           timestamp,
   primary key (id_barang)
);

/*==============================================================*/
/* Table: investor                                              */
/*==============================================================*/
create table investor
(
   id_investor          int not null,
   id_user              int,
   nama_investor        varchar(100),
   no_hp                varchar(20),
   email                varchar(100),
   created_at           timestamp,
   updated_at           timestamp,
   primary key (id_investor)
);

/*==============================================================*/
/* Table: laporan_omzet                                         */
/*==============================================================*/
create table laporan_omzet
(
   id_laporan           int not null,
   id_outlet            int,
   waktu_input          timestamp,
   omzet                decimal(15,2),
   presentase_potongan  decimal(5,2),
   nominal_potongan     decimal(15,2),
   created_at           timestamp,
   updated_at           timestamp,
   primary key (id_laporan)
);

/*==============================================================*/
/* Table: outlet                                                */
/*==============================================================*/
create table outlet
(
   id_outlet            int not null,
   id_user              int,
   id_investor          int,
   kode_outlet          varchar(20),
   nama_outlet          varchar(100),
   alamat               text,
   created_at           timestamp,
   updated_at           timestamp,
   primary key (id_outlet)
);

/*==============================================================*/
/* Table: pengaturan_sistem                                     */
/*==============================================================*/
create table pengaturan_sistem
(
   id_pengaturan        int not null,
   nama_pengaturan      varchar(50),
   nilai                decimal(5,2),
   updated_at           timestamp,
   primary key (id_pengaturan)
);

/*==============================================================*/
/* Table: rekap_bagi_hasil                                      */
/*==============================================================*/
create table rekap_bagi_hasil
(
   id_rekap             int not null,
   id_investor          int,
   periode_rekap        timestamp,
   akumulasi_omzet      decimal(15,2),
   akumulasi_potongan   decimal(15,2),
   hak_investor         decimal(15,2),
   hak_outlet           decimal(15,2),
   created_at           timestamp,
   primary key (id_rekap)
);

/*==============================================================*/
/* Table: users                                                 */
/*==============================================================*/
create table users
(
   id_user              int not null,
   id_investor          int,
   id_outlet            int,
   nama                 varchar(100),
   username             varchar(50),
   password             varchar(255),
   role                 enum('master', 'investor', 'outlet'),
   status               enum('aktif', 'nonaktif'),
   created_at           timestamp,
   updated_at           timestamp,
   primary key (id_user)
);

alter table users comment 'Pusat autentikasi untuk semua jenis pengguna yang akan login';

alter table barang add constraint fk_mengelola foreign key (id_outlet)
      references outlet (id_outlet) on delete restrict on update restrict;

alter table investor add constraint fk_mewakili2 foreign key (id_user)
      references users (id_user) on delete restrict on update restrict;

alter table laporan_omzet add constraint fk_mencatat foreign key (id_outlet)
      references outlet (id_outlet) on delete restrict on update restrict;

alter table outlet add constraint fk_ditugaskan_ke2 foreign key (id_user)
      references users (id_user) on delete restrict on update restrict;

alter table outlet add constraint fk_membawahi foreign key (id_investor)
      references investor (id_investor) on delete restrict on update restrict;

alter table rekap_bagi_hasil add constraint fk_mendapatkan foreign key (id_investor)
      references investor (id_investor) on delete restrict on update restrict;

alter table users add constraint fk_ditugaskan_ke foreign key (id_outlet)
      references outlet (id_outlet) on delete restrict on update restrict;

alter table users add constraint fk_mewakili foreign key (id_investor)
      references investor (id_investor) on delete restrict on update restrict;

